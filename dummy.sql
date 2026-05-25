-- ==========================================
-- SEED DATA for CS24 편의점 프로젝트
-- ==========================================

-- ==========================================
-- 1. 카테고리
-- ==========================================
INSERT INTO P_CATEGORY (categoryName, parentCategoryId, categoryDisplayOrder) VALUES
('식품', NULL, 1),
('음료', NULL, 2),
('생활용품', NULL, 3),
('즉석식품', NULL, 4),
('과자/스낵', NULL, 5);

-- 하위 카테고리
INSERT INTO P_CATEGORY (categoryName, parentCategoryId, categoryDisplayOrder) VALUES
('삼각김밥', 4, 1),
('도시락', 4, 2),
('샌드위치', 4, 3),
('라면/컵라면', 1, 4),
('유제품', 1, 5),
('탄산음료', 2, 1),
('에너지드링크', 2, 2),
('생수/차', 2, 3),
('커피음료', 2, 4),
('과자', 5, 1),
('초콜릿/캔디', 5, 2),
('위생용품', 3, 1),
('헬스/단백질', 1, 6);

-- ==========================================
-- 2. 매장 (경희대 내 7개 점포)
-- ==========================================
INSERT INTO P_STORE (storeName, storeAddress, storePhoneNumber, storeLatitude, storeLongitude, storeIsActive) VALUES
('공과대학점', '경기도 용인시 기흥구 덕영대로 1732 공과대학', '031-201-2001', 37.2412300, 127.0785100, true),
('우정원점', '경기도 용인시 기흥구 덕영대로 1732 우정원', '031-201-2002', 37.2398700, 127.0771400, true),
('제2기숙사점', '경기도 용인시 기흥구 덕영대로 1732 제2기숙사', '031-201-2003', 37.2381500, 127.0768900, true),
('전자정보대학점', '경기도 용인시 기흥구 덕영대로 1732 전자정보대학', '031-201-2004', 37.2419800, 127.0792300, true),
('중앙도서관점', '경기도 용인시 기흥구 덕영대로 1732 중앙도서관', '031-201-2005', 37.2407100, 127.0779600, true),
('예술디자인대학점', '경기도 용인시 기흥구 덕영대로 1732 예술디자인대학', '031-201-2006', 37.2425600, 127.0801200, true),
('체육대학점', '경기도 용인시 기흥구 덕영대로 1732 체육대학', '031-201-2007', 37.2433100, 127.0812700, true);

-- ==========================================
-- 3. 상품 50개 (실제 편의점 상품 기반)
-- ==========================================

-- 삼각김밥 (categoryId=6)
INSERT INTO P_PRODUCT (categoryId, productName, productPrice, productBarcode, promotionType) VALUES
(6, '참치마요삼각김밥', 1500.00, '8801234000001', 'NONE'),
(6, '불고기삼각김밥', 1500.00, '8801234000002', 'NONE'),
(6, '스팸마요삼각김밥', 1700.00, '8801234000003', 'ONE_PLUS_ONE'),
(6, '명란젓삼각김밥', 1800.00, '8801234000004', 'NONE'),
(6, '김치볶음밥삼각김밥', 1600.00, '8801234000005', 'NONE');

-- 도시락 (categoryId=7)
INSERT INTO P_PRODUCT (categoryId, productName, productPrice, productBarcode, promotionType) VALUES
(7, 'CU 제육볶음도시락', 4500.00, '8801234000010', 'NONE'),
(7, 'GS 불닭볶음도시락', 4800.00, '8801234000011', 'NONE'),
(7, '참치김치볶음밥도시락', 4200.00, '8801234000012', 'NONE');

-- 샌드위치 (categoryId=8)
INSERT INTO P_PRODUCT (categoryId, productName, productPrice, productBarcode, promotionType) VALUES
(8, '에그마요샌드위치', 2500.00, '8801234000020', 'NONE'),
(8, 'BLT샌드위치', 3200.00, '8801234000021', 'NONE'),
(8, '클럽샌드위치', 3500.00, '8801234000022', 'TWO_PLUS_ONE');

-- 라면/컵라면 (categoryId=9)
INSERT INTO P_PRODUCT (categoryId, productName, productPrice, productBarcode, promotionType) VALUES
(9, '신라면컵', 1200.00, '8801234000030', 'NONE'),
(9, '불닭볶음면컵', 1500.00, '8801234000031', 'ONE_PLUS_ONE'),
(9, '진라면컵(순한맛)', 1200.00, '8801234000032', 'NONE'),
(9, '팔도비빔면컵', 1300.00, '8801234000033', 'NONE');

-- 유제품 (categoryId=10)
INSERT INTO P_PRODUCT (categoryId, productName, productPrice, productBarcode, promotionType) VALUES
(10, '서울우유 200ml', 1100.00, '8801234000040', 'NONE'),
(10, '바나나맛우유', 1500.00, '8801234000041', 'NONE'),
(10, '딸기맛우유', 1500.00, '8801234000042', 'NONE'),
(10, '요플레 오리지널', 1200.00, '8801234000043', 'TWO_PLUS_ONE');

-- 탄산음료 (categoryId=11)
INSERT INTO P_PRODUCT (categoryId, productName, productPrice, productBarcode, promotionType) VALUES
(11, '펩시콜라 제로 500ml', 1800.00, '8801234000050', 'NONE'),
(11, '코카콜라 제로 500ml', 1800.00, '8801234000051', 'NONE'),
(11, '칠성사이다 500ml', 1700.00, '8801234000052', 'NONE'),
(11, '스프라이트 500ml', 1700.00, '8801234000053', 'NONE'),
(11, '닥터페퍼 250ml', 1600.00, '8801234000054', 'ONE_PLUS_ONE');

-- 에너지드링크 (categoryId=12)
INSERT INTO P_PRODUCT (categoryId, productName, productPrice, productBarcode, promotionType) VALUES
(12, '몬스터에너지 355ml', 2500.00, '8801234000060', 'NONE'),
(12, '레드불 250ml', 2800.00, '8801234000061', 'NONE'),
(12, '핫식스 250ml', 1500.00, '8801234000062', 'NONE');

-- 생수/차 (categoryId=13)
INSERT INTO P_PRODUCT (categoryId, productName, productPrice, productBarcode, promotionType) VALUES
(13, '제주삼다수 500ml', 1000.00, '8801234000070', 'NONE'),
(13, '아이시스 에코 500ml', 900.00, '8801234000071', 'NONE'),
(13, '티오피 아메리카노 500ml', 1700.00, '8801234000072', 'NONE'),
(13, '녹차원 500ml', 1500.00, '8801234000073', 'NONE');

-- 커피음료 (categoryId=14)
INSERT INTO P_PRODUCT (categoryId, productName, productPrice, productBarcode, promotionType) VALUES
(14, '스타벅스 더블샷 에스프레소 200ml', 3500.00, '8801234000080', 'NONE'),
(14, '맥심 티오피 마스터라떼 275ml', 2200.00, '8801234000081', 'NONE'),
(14, '레쓰비 카페라떼 240ml', 1500.00, '8801234000082', 'TWO_PLUS_ONE'),
(14, '조지아 오리지날 240ml', 1500.00, '8801234000083', 'NONE');

-- 과자 (categoryId=15)
INSERT INTO P_PRODUCT (categoryId, productName, productPrice, productBarcode, promotionType) VALUES
(15, '포카칩 오리지널 66g', 1800.00, '8801234000090', 'NONE'),
(15, '새우깡 90g', 1500.00, '8801234000091', 'NONE'),
(15, '꼬깔콘 72g', 1800.00, '8801234000092', 'NONE'),
(15, '오레오 쿠키 100g', 2000.00, '8801234000093', 'ONE_PLUS_ONE'),
(15, '빼빼로 초코 54g', 1500.00, '8801234000094', 'NONE');

-- 초콜릿/캔디 (categoryId=16)
INSERT INTO P_PRODUCT (categoryId, productName, productPrice, productBarcode, promotionType) VALUES
(16, '킷캣 미니 42g', 2000.00, '8801234000100', 'NONE'),
(16, '허쉬 초콜릿 40g', 2500.00, '8801234000101', 'NONE'),
(16, '꿀떡 젤리 50g', 1200.00, '8801234000102', 'TWO_PLUS_ONE');

-- 위생용품 (categoryId=17)
INSERT INTO P_PRODUCT (categoryId, productName, productPrice, productBarcode, promotionType) VALUES
(17, '크리넥스 미용티슈 60매', 2000.00, '8801234000110', 'NONE'),
(17, '도브 바디워시 미니 100ml', 3500.00, '8801234000111', 'NONE'),
(17, '페리오 칫솔 1개입', 2500.00, '8801234000112', 'NONE'),
(17, '면봉 100개입', 1500.00, '8801234000113', 'NONE'),
(17, '밴드 10개입', 2000.00, '8801234000114', 'NONE');

-- 헬스/단백질 (categoryId=18)
INSERT INTO P_PRODUCT (categoryId, productName, productPrice, productBarcode, promotionType) VALUES
(18, '하림 닭가슴살 닭갈비맛 100g', 2800.00, '8801234000120', 'NONE'),
(18, '하림 닭가슴살 오리지널 100g', 2500.00, '8801234000121', 'ONE_PLUS_ONE'),
(18, '동원 닭가슴살 스모크 100g', 2800.00, '8801234000122', 'NONE'),
(18, '마이프로틴 바 초코 70g', 3500.00, '8801234000123', 'NONE'),
(18, '프로틴 쉐이크 초코 250ml', 3800.00, '8801234000124', 'NONE');

-- ==========================================
-- 4. 테스트 유저 (id: test, pw: 1234)
-- ==========================================
INSERT INTO P_USER (userLoginId, userPassword, userName, userPhoneNumber, userEmail, userGrade, userBirthDate) VALUES
('test1', '1234', '홍길동1', '011-1234-5678', 'test@khu.ac.kr', 'GOLD', '2003-05-15');
-- 참고: 위 해시는 예시값입니다. 실제 환경에서는 password_hash('1234', PASSWORD_BCRYPT) 결과값으로 교체하세요.

-- ==========================================
-- 5. 재고 (7개 점포 × 50개 상품) 
-- (수정됨: 각 점포 블록 끝의 세미콜론을 쉼표로 변경하여 하나의 INSERT문으로 통일)
-- ==========================================

INSERT INTO P_STORE_INVENTORY (storeId, productId, inventoryQuantity) VALUES
-- 공과대학점 (storeId=1) - 학생 많아서 전반적으로 재고 많음
(1,1,45),(1,2,32),(1,3,28),(1,4,15),(1,5,20),
(1,6,18),(1,7,12),(1,8,10),(1,9,25),(1,10,14),
(1,11,8),(1,12,60),(1,13,45),(1,14,50),(1,15,40),
(1,16,30),(1,17,25),(1,18,20),(1,19,22),(1,20,80),
(1,21,75),(1,22,70),(1,23,65),(1,24,40),(1,25,35),
(1,26,30),(1,27,50),(1,28,45),(1,29,40),(1,30,35),
(1,31,25),(1,32,30),(1,33,20),(1,34,15),(1,35,45),
(1,36,40),(1,37,35),(1,38,30),(1,39,25),(1,40,20),
(1,41,15),(1,42,10),(1,43,18),(1,44,12),(1,45,8),
(1,46,55),(1,47,50),(1,48,30),(1,49,20),(1,50,15),

-- 우정원점 (storeId=2) - 기숙사 근처, 간식/음료 수요 높음
(2,1,30),(2,2,25),(2,3,35),(2,4,10),(2,5,18),
(2,6,8),(2,7,6),(2,8,5),(2,9,20),(2,10,12),
(2,11,10),(2,12,90),(2,13,85),(2,14,80),(2,15,70),
(2,16,40),(2,17,35),(2,18,30),(2,19,28),(2,20,100),
(2,21,95),(2,22,90),(2,23,85),(2,24,50),(2,25,45),
(2,26,40),(2,27,60),(2,28,55),(2,29,50),(2,30,45),
(2,31,30),(2,32,35),(2,33,25),(2,34,20),(2,35,55),
(2,36,50),(2,37,45),(2,38,40),(2,39,35),(2,40,25),
(2,41,10),(2,42,8),(2,43,15),(2,44,10),(2,45,6),
(2,46,40),(2,47,35),(2,48,25),(2,49,15),(2,50,10),

-- 제2기숙사점 (storeId=3)
(3,1,20),(3,2,18),(3,3,22),(3,4,8),(3,5,12),
(3,6,6),(3,7,4),(3,8,3),(3,9,15),(3,10,10),
(3,11,8),(3,12,70),(3,13,65),(3,14,60),(3,15,55),
(3,16,35),(3,17,30),(3,18,25),(3,19,22),(3,20,75),
(3,21,70),(3,22,65),(3,23,60),(3,24,40),(3,25,35),
(3,26,30),(3,27,45),(3,28,40),(3,29,35),(3,30,30),
(3,31,20),(3,32,25),(3,33,18),(3,34,15),(3,35,40),
(3,36,35),(3,37,30),(3,38,25),(3,39,20),(3,40,18),
(3,41,8),(3,42,6),(3,43,12),(3,44,8),(3,45,5),
(3,46,30),(3,47,25),(3,48,20),(3,49,12),(3,50,8),

-- 전자정보대학점 (storeId=4) - 에너지드링크/커피 수요 높음
(4,1,25),(4,2,20),(4,3,18),(4,4,12),(4,5,15),
(4,6,10),(4,7,8),(4,8,6),(4,9,18),(4,10,10),
(4,11,8),(4,12,50),(4,13,45),(4,14,40),(4,15,35),
(4,16,25),(4,17,20),(4,18,15),(4,19,18),(4,20,60),
(4,21,55),(4,22,50),(4,23,45),(4,24,60),(4,25,55),
(4,26,50),(4,27,35),(4,28,30),(4,29,60),(4,30,55),
(4,31,20),(4,32,25),(4,33,15),(4,34,12),(4,35,35),
(4,36,30),(4,37,25),(4,38,20),(4,39,15),(4,40,12),
(4,41,10),(4,42,8),(4,43,15),(4,44,10),(4,45,6),
(4,46,45),(4,47,40),(4,48,25),(4,49,18),(4,50,12),

-- 중앙도서관점 (storeId=5) - 조용, 커피/음료 수요
(5,1,15),(5,2,12),(5,3,10),(5,4,6),(5,5,8),
(5,6,5),(5,7,4),(5,8,3),(5,9,12),(5,10,8),
(5,11,6),(5,12,40),(5,13,35),(5,14,30),(5,15,25),
(5,16,20),(5,17,15),(5,18,12),(5,19,15),(5,20,45),
(5,21,40),(5,22,35),(5,23,30),(5,24,30),(5,25,25),
(5,26,20),(5,27,40),(5,28,35),(5,29,45),(5,30,40),
(5,31,15),(5,32,18),(5,33,12),(5,34,10),(5,35,25),
(5,36,20),(5,37,15),(5,38,12),(5,39,10),(5,40,8),
(5,41,6),(5,42,5),(5,43,10),(5,44,8),(5,45,4),
(5,46,20),(5,47,18),(5,48,15),(5,49,10),(5,50,8),

-- 예술디자인대학점 (storeId=6)
(6,1,18),(6,2,15),(6,3,12),(6,4,8),(6,5,10),
(6,6,6),(6,7,5),(6,8,4),(6,9,14),(6,10,9),
(6,11,7),(6,12,35),(6,13,30),(6,14,28),(6,15,22),
(6,16,18),(6,17,14),(6,18,10),(6,19,12),(6,20,40),
(6,21,35),(6,22,30),(6,23,28),(6,24,25),(6,25,20),
(6,26,18),(6,27,30),(6,28,25),(6,29,35),(6,30,30),
(6,31,12),(6,32,15),(6,33,10),(6,34,8),(6,35,22),
(6,36,18),(6,37,14),(6,38,10),(6,39,8),(6,40,6),
(6,41,5),(6,42,4),(6,43,8),(6,44,6),(6,45,3),
(6,46,18),(6,47,15),(6,48,12),(6,49,8),(6,50,6),

-- 체육대학점 (storeId=7) - 단백질/헬스 상품 수요 높음
(7,1,20),(7,2,18),(7,3,15),(7,4,10),(7,5,12),
(7,6,8),(7,7,6),(7,8,5),(7,9,16),(7,10,10),
(7,11,8),(7,12,30),(7,13,25),(7,14,22),(7,15,18),
(7,16,15),(7,17,12),(7,18,10),(7,19,12),(7,20,35),
(7,21,30),(7,22,25),(7,23,22),(7,24,20),(7,25,18),
(7,26,15),(7,27,25),(7,28,20),(7,29,30),(7,30,25),
(7,31,10),(7,32,12),(7,33,8),(7,34,6),(7,35,18),
(7,36,15),(7,37,12),(7,38,10),(7,39,8),(7,40,6),
(7,41,5),(7,42,4),(7,43,8),(7,44,6),(7,45,3),
(7,46,80),(7,47,75),(7,48,60),(7,49,45),(7,50,35);

-- ==========================================
-- 6. 테스트 유저 구매 내역 (userId=1)
-- ==========================================

-- 주문 1: 중앙도서관점 픽업 (2주 전)
INSERT INTO P_ORDER (userId, storeId, orderTotalAmount, orderPaymentMethod, orderStatus, orderIsDelivery, orderPickupCode, orderPaidAt, createdAt, updatedAt)
VALUES (1, 5, 7000.00, 'CARD', 'PICKED_UP', false, 'PICK-001', '2026-05-06 14:30:00', '2026-05-06 14:25:00', '2026-05-06 14:35:00');

INSERT INTO P_ORDER_DETAIL (orderId, productId, orderDetailQuantity, orderDetailUnitPrice, orderDetailSubtotal, createdAt)
VALUES
(1, 1, 1, 1500.00, 1500.00, '2026-05-06 14:25:00'),  -- 참치마요삼각김밥
(1, 29, 1, 3500.00, 3500.00, '2026-05-06 14:25:00'), -- 스타벅스 더블샷
(1, 27, 1, 1700.00, 1700.00, '2026-05-06 14:25:00'); -- 티오피 아메리카노

INSERT INTO P_PAYMENT (orderId, paymentMethod, paymentTransactionId, paymentPaidAmount, paymentStatus, paymentPgProvider, paymentApprovedAt, createdAt)
VALUES (1, 'CARD', 'TXN-20260506-001', 7000.00, 'APPROVED', 'KAKAOPAY', '2026-05-06 14:30:00', '2026-05-06 14:30:00');

-- 주문 2: 공과대학점 픽업 (1주 전)
INSERT INTO P_ORDER (userId, storeId, orderTotalAmount, orderPaymentMethod, orderStatus, orderIsDelivery, orderPickupCode, orderPaidAt, createdAt, updatedAt)
VALUES (1, 1, 9300.00, 'CARD', 'PICKED_UP', false, 'PICK-002', '2026-05-13 12:10:00', '2026-05-13 12:05:00', '2026-05-13 12:20:00');

INSERT INTO P_ORDER_DETAIL (orderId, productId, orderDetailQuantity, orderDetailUnitPrice, orderDetailSubtotal, createdAt)
VALUES
(2, 46, 1, 2800.00, 2800.00, '2026-05-13 12:05:00'), -- 하림 닭가슴살 닭갈비맛
(2, 47, 1, 2500.00, 2500.00, '2026-05-13 12:05:00'), -- 하림 닭가슴살 오리지널
(2, 20, 1, 1800.00, 1800.00, '2026-05-13 12:05:00'), -- 펩시콜라 제로
(2, 27, 1, 1700.00, 1700.00, '2026-05-13 12:05:00'); -- 티오피 아메리카노

INSERT INTO P_PAYMENT (orderId, paymentMethod, paymentTransactionId, paymentPaidAmount, paymentStatus, paymentPgProvider, paymentApprovedAt, createdAt)
VALUES (2, 'CARD', 'TXN-20260513-001', 9300.00, 'APPROVED', 'TOSS', '2026-05-13 12:10:00', '2026-05-13 12:10:00');

-- 주문 3: 전자정보대학점 픽업 (어제)
INSERT INTO P_ORDER (userId, storeId, orderTotalAmount, orderPaymentMethod, orderStatus, orderIsDelivery, orderPickupCode, orderPaidAt, createdAt, updatedAt)
VALUES (1, 4, 5300.00, 'CARD', 'PICKED_UP', false, 'PICK-003', '2026-05-19 18:45:00', '2026-05-19 18:40:00', '2026-05-19 18:50:00');

INSERT INTO P_ORDER_DETAIL (orderId, productId, orderDetailQuantity, orderDetailUnitPrice, orderDetailSubtotal, createdAt)
VALUES
(3, 25, 1, 2500.00, 2500.00, '2026-05-19 18:40:00'), -- 몬스터에너지
(3, 12, 1, 1200.00, 1200.00, '2026-05-19 18:40:00'), -- 신라면컵
(3, 9, 1, 2500.00, 2500.00, '2026-05-19 18:40:00');  -- 에그마요샌드위치 (1300원 할인쿠폰 적용 가정)

INSERT INTO P_PAYMENT (orderId, paymentMethod, paymentTransactionId, paymentPaidAmount, paymentStatus, paymentPgProvider, paymentApprovedAt, createdAt)
VALUES (3, 'CARD', 'TXN-20260519-001', 5300.00, 'APPROVED', 'KAKAOPAY', '2026-05-19 18:45:00', '2026-05-19 18:45:00');

-- 주문 4: 우정원점 픽업 (오늘, 결제 완료 후 픽업 대기 중)
INSERT INTO P_ORDER (userId, storeId, orderTotalAmount, orderPaymentMethod, orderStatus, orderIsDelivery, orderPickupCode, orderPaidAt, createdAt, updatedAt)
VALUES (1, 2, 6000.00, 'CARD', 'READY', false, 'PICK-004', '2026-05-20 10:00:00', '2026-05-20 09:55:00', '2026-05-20 10:05:00');

INSERT INTO P_ORDER_DETAIL (orderId, productId, orderDetailQuantity, orderDetailUnitPrice, orderDetailSubtotal, createdAt)
VALUES
(4, 3, 2, 1700.00, 3400.00, '2026-05-20 09:55:00'),  -- 스팸마요삼각김밥 2개 (1+1 행사)
(4, 50, 1, 3800.00, 3800.00, '2026-05-20 09:55:00'); -- 프로틴 쉐이크 (할인 적용)

INSERT INTO P_PAYMENT (orderId, paymentMethod, paymentTransactionId, paymentPaidAmount, paymentStatus, paymentPgProvider, paymentApprovedAt, createdAt)
VALUES (4, 'CARD', 'TXN-20260520-001', 6000.00, 'APPROVED', 'KAKAOPAY', '2026-05-20 10:00:00', '2026-05-20 10:00:00');

-- 주문 5: 제2기숙사점 배송 주문
INSERT INTO P_ORDER (userId, storeId, orderTotalAmount, orderPaymentMethod, orderStatus, orderIsDelivery, orderPaidAt, createdAt, updatedAt)
VALUES (1, 3, 12800.00, 'CARD', 'PAID', true, '2026-05-20 11:30:00', '2026-05-20 11:25:00', '2026-05-20 11:30:00');

INSERT INTO P_ORDER_DETAIL (orderId, productId, orderDetailQuantity, orderDetailUnitPrice, orderDetailSubtotal, createdAt)
VALUES
(5, 46, 2, 2800.00, 5600.00, '2026-05-20 11:25:00'), -- 하림 닭가슴살 2개
(5, 50, 1, 3800.00, 3800.00, '2026-05-20 11:25:00'), -- 프로틴 쉐이크
(5, 21, 1, 1800.00, 1800.00, '2026-05-20 11:25:00'), -- 코카콜라 제로
(5, 27, 1, 1700.00, 1700.00, '2026-05-20 11:25:00'); -- 티오피 아메리카노

INSERT INTO P_DELIVERY (orderId, deliveryRecipientName, deliveryPhoneNumber, deliveryAddress, deliveryAddressDetail, deliveryZipCode, deliveryRequestMemo, deliveryFee, deliveryStatus, createdAt, updatedAt)
VALUES (5, '김경희', '010-1234-5678', '경기도 용인시 기흥구 덕영대로 1732', '제2기숙사 302호', '17104', '문 앞에 놓아주세요', 3000.00, 'PREPARING', '2026-05-20 11:30:00', '2026-05-20 11:30:00');

INSERT INTO P_PAYMENT (orderId, paymentMethod, paymentTransactionId, paymentPaidAmount, paymentStatus, paymentPgProvider, paymentApprovedAt, createdAt)
VALUES (5, 'CARD', 'TXN-20260520-002', 12800.00, 'APPROVED', 'TOSS', '2026-05-20 11:30:00', '2026-05-20 11:30:00');

-- ==========================================
-- 7. 1+1 행사 보관함 (주문2의 하림 닭가슴살 오리지널 1+1)
-- ==========================================
INSERT INTO P_STORAGE (userId, productId, orderDetailId, storageQuantity, storageStatus, storageExpireAt, createdAt, updatedAt)
VALUES (1, 47, 5, 1, 'AVAILABLE', '2026-06-13 23:59:59', '2026-05-13 12:20:00', '2026-05-13 12:20:00');

