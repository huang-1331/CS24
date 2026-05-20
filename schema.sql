-- CS24 편의점 데이터베이스 스키마
-- 재임포트가 가능하도록 기존 테이블을 먼저 제거한다.
SET FOREIGN_KEY_CHECKS = 0;
DROP TABLE IF EXISTS P_POINT_HISTORY;
DROP TABLE IF EXISTS P_STORAGE;
DROP TABLE IF EXISTS P_PAYMENT;
DROP TABLE IF EXISTS P_DELIVERY;
DROP TABLE IF EXISTS P_ORDER_DETAIL;
DROP TABLE IF EXISTS P_ORDER;
DROP TABLE IF EXISTS P_CART;
DROP TABLE IF EXISTS P_STORE_INVENTORY;
DROP TABLE IF EXISTS P_PRODUCT;
DROP TABLE IF EXISTS P_CATEGORY;
DROP TABLE IF EXISTS P_STORE;
DROP TABLE IF EXISTS P_USER;
SET FOREIGN_KEY_CHECKS = 1;

CREATE TABLE P_USER (
  userId bigint PRIMARY KEY AUTO_INCREMENT,
  userLoginId varchar(50) UNIQUE NOT NULL,
  userPassword varchar(255) NOT NULL COMMENT '해시된 비밀번호',
  userName varchar(50) NOT NULL,
  userPhoneNumber varchar(20) UNIQUE NOT NULL,
  userEmail varchar(100) UNIQUE,
  userPoint int NOT NULL DEFAULT 0 COMMENT '현재 포인트 잔액 (원장: P_POINT_HISTORY)',
  userGrade ENUM ('BRONZE', 'SILVER', 'GOLD', 'VIP') NOT NULL DEFAULT 'BRONZE',
  userBirthDate date,
  deletedAt datetime COMMENT 'soft delete',
  createdAt datetime NOT NULL DEFAULT (now()) COMMENT '생성 일시',
  updatedAt datetime NOT NULL DEFAULT (now()) COMMENT '수정 일시'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE P_STORE (
  storeId bigint PRIMARY KEY AUTO_INCREMENT,
  storeName varchar(100) NOT NULL,
  storeAddress varchar(255) NOT NULL,
  storePhoneNumber varchar(20) NOT NULL,
  storeLatitude decimal(10,7),
  storeLongitude decimal(10,7),
  storeIsActive boolean NOT NULL DEFAULT true,
  deletedAt datetime COMMENT 'soft delete',
  createdAt datetime NOT NULL DEFAULT (now()) COMMENT '생성 일시',
  updatedAt datetime NOT NULL DEFAULT (now()) COMMENT '수정 일시'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE P_CATEGORY (
  categoryId bigint PRIMARY KEY AUTO_INCREMENT,
  categoryName varchar(100) UNIQUE NOT NULL,
  parentCategoryId bigint COMMENT '상위 카테고리(계층)',
  categoryDisplayOrder int NOT NULL DEFAULT 0,
  createdAt datetime NOT NULL DEFAULT (now()) COMMENT '생성 일시',
  updatedAt datetime NOT NULL DEFAULT (now()) COMMENT '수정 일시'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE P_PRODUCT (
  productId bigint PRIMARY KEY AUTO_INCREMENT,
  categoryId bigint NOT NULL,
  productName varchar(150) NOT NULL,
  productPrice decimal(12,2) NOT NULL,
  productBarcode varchar(50) UNIQUE,
  productImageUrl varchar(500),
  productDescription text,
  promotionType ENUM ('NONE', 'ONE_PLUS_ONE', 'TWO_PLUS_ONE', 'DISCOUNT') NOT NULL DEFAULT 'NONE',
  productIsActive boolean NOT NULL DEFAULT true,
  deletedAt datetime COMMENT 'soft delete',
  createdAt datetime NOT NULL DEFAULT (now()) COMMENT '생성 일시',
  updatedAt datetime NOT NULL DEFAULT (now()) COMMENT '수정 일시'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE P_STORE_INVENTORY (
  inventoryId bigint PRIMARY KEY AUTO_INCREMENT,
  storeId bigint NOT NULL,
  productId bigint NOT NULL,
  inventoryQuantity int NOT NULL DEFAULT 0,
  createdAt datetime NOT NULL DEFAULT (now()) COMMENT '생성 일시',
  updatedAt datetime NOT NULL DEFAULT (now()) COMMENT '수정 일시'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE P_CART (
  cartId bigint PRIMARY KEY AUTO_INCREMENT,
  userId bigint NOT NULL,
  storeId bigint NOT NULL,
  productId bigint NOT NULL,
  cartQuantity int NOT NULL DEFAULT 1,
  createdAt datetime NOT NULL DEFAULT (now()) COMMENT '생성 일시',
  updatedAt datetime NOT NULL DEFAULT (now()) COMMENT '수정 일시'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE P_ORDER (
  orderId bigint PRIMARY KEY AUTO_INCREMENT,
  userId bigint NOT NULL,
  storeId bigint NOT NULL,
  orderTotalAmount decimal(12,2) NOT NULL DEFAULT 0,
  orderUsedPoint int NOT NULL DEFAULT 0,
  orderEarnedPoint int NOT NULL DEFAULT 0,
  orderPaymentMethod ENUM ('CARD', 'POINT', 'MIXED') NOT NULL DEFAULT 'CARD' COMMENT '최종 결제수단 요약 (원장: P_PAYMENT)',
  orderStatus ENUM ('PENDING', 'PAID', 'READY', 'PICKED_UP', 'CANCELED') NOT NULL DEFAULT 'PENDING',
  orderIsDelivery boolean NOT NULL DEFAULT false,
  orderPickupCode varchar(20) UNIQUE COMMENT '픽업 인증 코드',
  orderPaidAt datetime COMMENT '최종 결제 완료 시각 캐시',
  createdAt datetime NOT NULL DEFAULT (now()) COMMENT '생성 일시',
  updatedAt datetime NOT NULL DEFAULT (now()) COMMENT '수정 일시'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE P_ORDER_DETAIL (
  orderDetailId bigint PRIMARY KEY AUTO_INCREMENT,
  orderId bigint NOT NULL,
  productId bigint NOT NULL,
  orderDetailQuantity int NOT NULL,
  orderDetailUnitPrice decimal(12,2) NOT NULL COMMENT '주문 시점 단가 스냅샷',
  orderDetailSubtotal decimal(12,2) NOT NULL,
  createdAt datetime NOT NULL DEFAULT (now()) COMMENT '생성 일시 (append-only)'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE P_DELIVERY (
  deliveryId bigint PRIMARY KEY AUTO_INCREMENT,
  orderId bigint UNIQUE NOT NULL COMMENT '1:1 강제',
  deliveryRecipientName varchar(50) NOT NULL,
  deliveryPhoneNumber varchar(20) NOT NULL,
  deliveryAddress varchar(255) NOT NULL,
  deliveryAddressDetail varchar(255),
  deliveryZipCode varchar(10),
  deliveryRequestMemo varchar(500),
  deliveryFee decimal(10,2) NOT NULL DEFAULT 0,
  deliveryStatus ENUM ('REQUESTED', 'PREPARING', 'OUT_FOR_DELIVERY', 'DELIVERED', 'FAILED') NOT NULL DEFAULT 'REQUESTED',
  deliveryDispatchedAt datetime,
  deliveryCompletedAt datetime,
  createdAt datetime NOT NULL DEFAULT (now()) COMMENT '생성 일시',
  updatedAt datetime NOT NULL DEFAULT (now()) COMMENT '수정 일시'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE P_PAYMENT (
  paymentId bigint PRIMARY KEY AUTO_INCREMENT,
  orderId bigint NOT NULL,
  paymentMethod ENUM ('CARD', 'POINT', 'MIXED') NOT NULL,
  paymentTransactionId varchar(100) UNIQUE COMMENT 'PG사 거래 고유번호',
  paymentPaidAmount decimal(12,2) NOT NULL COMMENT '+ 결제 / - 환불',
  paymentStatus ENUM ('REQUESTED', 'APPROVED', 'FAILED', 'REFUNDED', 'PARTIAL_REFUNDED') NOT NULL DEFAULT 'REQUESTED',
  paymentPgProvider varchar(50) COMMENT 'KAKAOPAY, TOSS, NICEPAY 등',
  paymentApprovedAt datetime,
  paymentFailReason varchar(255),
  createdAt datetime NOT NULL DEFAULT (now()) COMMENT '생성 일시 (append-only)'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE P_STORAGE (
  storageId bigint PRIMARY KEY AUTO_INCREMENT,
  userId bigint NOT NULL,
  productId bigint NOT NULL,
  orderDetailId bigint COMMENT '획득 출처 주문 상세',
  storageQuantity int NOT NULL DEFAULT 1 COMMENT '동일 상품 다중 보관 수량',
  storageStatus ENUM ('AVAILABLE', 'USED', 'EXPIRED') NOT NULL DEFAULT 'AVAILABLE',
  storageExpireAt datetime NOT NULL,
  storageRedeemedAt datetime,
  createdAt datetime NOT NULL DEFAULT (now()) COMMENT '생성 일시',
  updatedAt datetime NOT NULL DEFAULT (now()) COMMENT '수정 일시'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE P_POINT_HISTORY (
  pointHistoryId bigint PRIMARY KEY AUTO_INCREMENT,
  userId bigint NOT NULL,
  orderId bigint COMMENT '관련 주문 (이벤트/조정 시 NULL)',
  pointChangeType ENUM ('EARN', 'USE', 'EXPIRE', 'ADMIN_ADJUST', 'CANCEL') NOT NULL,
  pointAmount int NOT NULL COMMENT '+적립 / -차감',
  pointBalanceAfter int NOT NULL COMMENT '거래 직후 잔액 스냅샷',
  pointReason varchar(255),
  createdAt datetime NOT NULL DEFAULT (now()) COMMENT '생성 일시 (append-only)'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 인덱스 설정
CREATE UNIQUE INDEX idx_user_loginId ON P_USER (userLoginId);
CREATE INDEX idx_user_phone ON P_USER (userPhoneNumber);
CREATE INDEX idx_user_deletedAt ON P_USER (deletedAt);

CREATE INDEX idx_store_name ON P_STORE (storeName);
CREATE INDEX idx_store_geo ON P_STORE (storeLatitude, storeLongitude);
CREATE INDEX idx_store_deletedAt ON P_STORE (deletedAt);

CREATE INDEX idx_category_parent ON P_CATEGORY (parentCategoryId);

CREATE INDEX idx_product_category ON P_PRODUCT (categoryId);
CREATE INDEX idx_product_name ON P_PRODUCT (productName);
CREATE INDEX idx_product_promotion ON P_PRODUCT (promotionType);
CREATE INDEX idx_product_deletedAt ON P_PRODUCT (deletedAt);

CREATE UNIQUE INDEX uq_inventory_store_product ON P_STORE_INVENTORY (storeId, productId);
CREATE INDEX idx_inventory_product ON P_STORE_INVENTORY (productId);

CREATE UNIQUE INDEX uq_cart_user_store_product ON P_CART (userId, storeId, productId);
CREATE INDEX idx_cart_user ON P_CART (userId);

CREATE INDEX idx_order_user ON P_ORDER (userId);
CREATE INDEX idx_order_store ON P_ORDER (storeId);
CREATE INDEX idx_order_status ON P_ORDER (orderStatus);
CREATE INDEX idx_order_createdAt ON P_ORDER (createdAt) USING BTREE;

CREATE INDEX idx_order_detail_order ON P_ORDER_DETAIL (orderId);
CREATE INDEX idx_order_detail_product ON P_ORDER_DETAIL (productId);

CREATE INDEX idx_delivery_status ON P_DELIVERY (deliveryStatus);

CREATE INDEX idx_payment_order ON P_PAYMENT (orderId);
CREATE INDEX idx_payment_status ON P_PAYMENT (paymentStatus);

CREATE INDEX idx_storage_user ON P_STORAGE (userId);
CREATE INDEX idx_storage_status ON P_STORAGE (storageStatus);
CREATE INDEX idx_storage_expire ON P_STORAGE (storageExpireAt);

CREATE INDEX idx_point_history_user ON P_POINT_HISTORY (userId);
CREATE INDEX idx_point_history_order ON P_POINT_HISTORY (orderId);
CREATE INDEX idx_point_history_type ON P_POINT_HISTORY (pointChangeType);
CREATE INDEX idx_point_history_createdAt ON P_POINT_HISTORY (createdAt) USING BTREE;

-- 테이블 코멘트 설정
ALTER TABLE P_USER COMMENT = '회원 마스터 (soft delete)';
ALTER TABLE P_STORE COMMENT = '매장(지점) 정보 (soft delete)';
ALTER TABLE P_CATEGORY COMMENT = '상품 카테고리 (계층형)';
ALTER TABLE P_PRODUCT COMMENT = '상품 마스터 (soft delete)';
ALTER TABLE P_STORE_INVENTORY COMMENT = '매장별 상품 재고';
ALTER TABLE P_CART COMMENT = '픽업 장바구니';
ALTER TABLE P_ORDER COMMENT = '주문 (픽업 기본, isDelivery=true 시 배송)';
ALTER TABLE P_ORDER_DETAIL COMMENT = '주문 상세 라인 (불변, append-only)';
ALTER TABLE P_DELIVERY COMMENT = '배송 정보 (P_ORDER와 1:0..1)';
ALTER TABLE P_PAYMENT COMMENT = '결제 트랜잭션 원장 (append-only)';
ALTER TABLE P_STORAGE COMMENT = '1+1/2+1 행사 보관함';
ALTER TABLE P_POINT_HISTORY COMMENT = '포인트 적립/차감 감사 원장 (append-only)';

-- 외래키(관계) 설정
ALTER TABLE P_CATEGORY ADD FOREIGN KEY (parentCategoryId) REFERENCES P_CATEGORY (categoryId) ON DELETE SET NULL ON UPDATE CASCADE;

ALTER TABLE P_PRODUCT ADD FOREIGN KEY (categoryId) REFERENCES P_CATEGORY (categoryId) ON DELETE RESTRICT ON UPDATE CASCADE;

ALTER TABLE P_STORE_INVENTORY ADD FOREIGN KEY (storeId) REFERENCES P_STORE (storeId) ON DELETE RESTRICT ON UPDATE CASCADE;
ALTER TABLE P_STORE_INVENTORY ADD FOREIGN KEY (productId) REFERENCES P_PRODUCT (productId) ON DELETE RESTRICT ON UPDATE CASCADE;

ALTER TABLE P_CART ADD FOREIGN KEY (userId) REFERENCES P_USER (userId) ON DELETE RESTRICT ON UPDATE CASCADE;
ALTER TABLE P_CART ADD FOREIGN KEY (storeId) REFERENCES P_STORE (storeId) ON DELETE RESTRICT ON UPDATE CASCADE;
ALTER TABLE P_CART ADD FOREIGN KEY (productId) REFERENCES P_PRODUCT (productId) ON DELETE RESTRICT ON UPDATE CASCADE;

ALTER TABLE P_ORDER ADD FOREIGN KEY (userId) REFERENCES P_USER (userId) ON DELETE RESTRICT ON UPDATE CASCADE;
ALTER TABLE P_ORDER ADD FOREIGN KEY (storeId) REFERENCES P_STORE (storeId) ON DELETE RESTRICT ON UPDATE CASCADE;

ALTER TABLE P_ORDER_DETAIL ADD FOREIGN KEY (orderId) REFERENCES P_ORDER (orderId) ON DELETE RESTRICT ON UPDATE CASCADE;
ALTER TABLE P_ORDER_DETAIL ADD FOREIGN KEY (productId) REFERENCES P_PRODUCT (productId) ON DELETE RESTRICT ON UPDATE CASCADE;

ALTER TABLE P_DELIVERY ADD FOREIGN KEY (orderId) REFERENCES P_ORDER (orderId) ON DELETE RESTRICT ON UPDATE CASCADE;

ALTER TABLE P_PAYMENT ADD FOREIGN KEY (orderId) REFERENCES P_ORDER (orderId) ON DELETE RESTRICT ON UPDATE CASCADE;

ALTER TABLE P_STORAGE ADD FOREIGN KEY (userId) REFERENCES P_USER (userId) ON DELETE RESTRICT ON UPDATE CASCADE;
ALTER TABLE P_STORAGE ADD FOREIGN KEY (productId) REFERENCES P_PRODUCT (productId) ON DELETE RESTRICT ON UPDATE CASCADE;
ALTER TABLE P_STORAGE ADD FOREIGN KEY (orderDetailId) REFERENCES P_ORDER_DETAIL (orderDetailId) ON DELETE SET NULL ON UPDATE CASCADE;

ALTER TABLE P_POINT_HISTORY ADD FOREIGN KEY (userId) REFERENCES P_USER (userId) ON DELETE RESTRICT ON UPDATE CASCADE;
ALTER TABLE P_POINT_HISTORY ADD FOREIGN KEY (orderId) REFERENCES P_ORDER (orderId) ON DELETE SET NULL ON UPDATE CASCADE;
