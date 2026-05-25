# CLAUDE.md

This file provides guidance to Claude Code (claude.ai/code) when working with code in this repository.

## Project Overview

CS24는 PHP + MySQL로 구현한 편의점 웹 애플리케이션(데이터베이스 과제용)입니다. 프레임워크 없이 순수 PHP로 작성되었으며, Tailwind CSS(CDN)로 스타일링합니다. 빌드 도구, 패키지 매니저, 테스트 스위트는 없습니다.

## Architecture

### Request Flow

모든 PHP 페이지가 동일한 패턴을 따릅니다:

```
require 'db.php'         ← $conn 생성 + h() 헬퍼 정의
session_start()
인증 가드 (미로그인 → login.php)
비즈니스 로직 + DB 쿼리
$pageTitle 설정
require 'header.php'     ← HTML head + 내비게이션 출력
HTML 본문 출력
require 'footer.php'     ← </body></html>
```

`header.php`를 require하기 **전에** `session_start()`와 `$pageTitle` 설정이 완료되어야 합니다.

### Key Files

| 파일 | 역할 |
|------|------|
| `db.php` | DB 연결 싱글턴 + `h()` XSS 이스케이프 헬퍼 |
| `cart_panel.php` | 장바구니 UI 모듈 — `require` 또는 AJAX 직접 호출 양쪽 지원 |
| `cart_process.php` | 장바구니 add/clear 처리 — AJAX 시 204/409, 비-AJAX 시 redirect |
| `checkout.php` | GET=주문 확인 화면, POST=트랜잭션 처리 (재고 차감 + 결제 + 카트 비우기) |
| `schema.sql` | 전체 DDL (soft delete, append-only 테이블 포함) |
| `seed.sql` | 데모 데이터 |

### Database Schema (핵심 관계)

```
P_USER ─── P_CART ─────────── P_STORE
              │                   │
         P_PRODUCT ──── P_STORE_INVENTORY
              │
         P_ORDER ──── P_ORDER_DETAIL
              │            │
         P_DELIVERY    P_STORAGE   ← 1+1/2+1 증정품 보관함
         P_PAYMENT                 ← append-only 결제 원장
         P_POINT_HISTORY           ← append-only 포인트 원장
```

- `P_CART`: `(userId, storeId, productId)` UNIQUE — 한 사용자가 한 번에 **한 매장**만 담을 수 있음
- `P_ORDER_DETAIL`, `P_PAYMENT`, `P_POINT_HISTORY`: append-only (UPDATE 금지)
- 모든 주요 테이블은 `deletedAt` soft delete 사용

### Cart System

장바구니는 "휘발성(volatile)"으로 설계되었습니다:
- 타 페이지로 이탈 시 `beforeunload`/`pagehide`에서 자동 비우기
- 면제: `checkout.php`(결제), `products.php`(추가 담기)
- 교차 매장 차단: 다른 매장 아이템 존재 시 HTTP 409 반환

`cart_panel.php`는 두 가지 방식으로 호출됩니다:
1. `products.php`에서 `require` — 호출자가 `$storeId` 변수를 미리 설정 (빈 카트일 때 체크아웃 링크의 폴백 storeId로 사용)
2. AJAX `GET cart_panel.php?storeId=N` — 자체 세션/DB 부트스트랩

`cart_panel.php`는 storeId 필터 없이 사용자의 실제 P_CART 전체를 조회합니다. 체크아웃 링크 및 비우기 대상 storeId(`$effectiveStoreId`)는 실제 담긴 항목의 storeId에서 결정됩니다.

### Checkout Transaction (checkout.php POST)

단일 트랜잭션 내에서 순서대로 처리:
1. 카트 라인 조회 + 재고 검증
2. `P_ORDER` 생성 (픽업 코드 발급)
3. `P_ORDER_DETAIL` 삽입 + 재고 차감 (`inventoryQuantity >= qty` 조건부 UPDATE)
4. 행사 상품 증정품 → `P_STORAGE` 적재 (30일 유효)
5. Mock 결제 기록 (`P_PAYMENT`, status=APPROVED)
6. 카트 비우기

실패 시 `rollback()`.

## Code Conventions

- **XSS 방지**: 모든 출력은 `h()` 래핑 (`htmlspecialchars` ENT_QUOTES)
- **SQL 인젝션 방지**: 모든 쿼리는 prepared statement (`$conn->prepare()` + `bind_param`)
- **오류 처리**: `mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT)` — DB 오류는 예외로 전환
- **AJAX 감지**: `$_SERVER['HTTP_X_REQUESTED_WITH'] === 'XMLHttpRequest'`
- **프로모션 타입**: `ONE_PLUS_ONE`, `TWO_PLUS_ONE`, `DISCOUNT`, `NONE`
- **결제**: Mock 처리 (실제 PG 연동 없음), 트랜잭션 ID는 `MOCK-` 접두사

---

## Working Guidelines

### 1. Think Before Coding

Don't assume. Don't hide confusion. Surface tradeoffs.

Before implementing:
- State your assumptions explicitly. If uncertain, ask.
- If multiple interpretations exist, present them — don't pick silently.
- If a simpler approach exists, say so. Push back when warranted.
- If something is unclear, stop. Name what's confusing. Ask.

### 2. Simplicity First

Minimum code that solves the problem. Nothing speculative.

- No features beyond what was asked.
- No abstractions for single-use code.
- No "flexibility" or "configurability" that wasn't requested.
- No error handling for impossible scenarios.
- If you write 200 lines and it could be 50, rewrite it.

Ask yourself: "Would a senior engineer say this is overcomplicated?" If yes, simplify.

### 3. Surgical Changes

Touch only what you must. Clean up only your own mess.

When editing existing code:
- Don't "improve" adjacent code, comments, or formatting.
- Don't refactor things that aren't broken.
- Match existing style, even if you'd do it differently.
- If you notice unrelated dead code, mention it — don't delete it.

When your changes create orphans:
- Remove imports/variables/functions that YOUR changes made unused.
- Don't remove pre-existing dead code unless asked.

The test: Every changed line should trace directly to the user's request.

### 4. Goal-Driven Execution

Define success criteria. Loop until verified.

Transform tasks into verifiable goals:
- "Add validation" → "Write tests for invalid inputs, then make them pass"
- "Fix the bug" → "Write a test that reproduces it, then make it pass"
- "Refactor X" → "Ensure tests pass before and after"

For multi-step tasks, state a brief plan:
```
1. [Step] → verify: [check]
2. [Step] → verify: [check]
3. [Step] → verify: [check]
```

Strong success criteria let you loop independently. Weak criteria ("make it work") require constant clarification.
