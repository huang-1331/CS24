-- CS24 데모 데이터
-- schema.sql 임포트 후 실행한다: mysql DB < schema.sql && mysql DB < seed.sql

INSERT INTO P_CATEGORY (categoryName, categoryDisplayOrder) VALUES
('음료', 1),
('스낵', 2),
('식품', 3),
('생활용품', 4);

INSERT INTO P_STORE (storeName, storeAddress, storePhoneNumber) VALUES
('CS24 강남점', '서울시 강남구 테헤란로 123', '02-111-1111'),
('CS24 신촌점', '서울시 서대문구 신촌로 45', '02-222-2222'),
('CS24 홍대점', '서울시 마포구 홍익로 67', '02-333-3333');

INSERT INTO P_PRODUCT (categoryId, productName, productPrice, promotionType) VALUES
(1, '코카콜라 500ml', 2000, 'NONE'),
(1, '제주 삼다수 2L', 1100, 'NONE'),
(1, '카페라떼 캔커피', 1800, 'NONE'),
(1, '핫식스 에너지드링크', 1700, 'DISCOUNT'),
(2, '새우깡', 1500, 'NONE'),
(2, '포카칩 오리지널', 1800, 'NONE'),
(2, '초코파이 12개입', 4800, 'NONE'),
(3, '삼각김밥 참치마요', 1300, 'ONE_PLUS_ONE'),
(3, '컵라면 큰컵', 1500, 'NONE'),
(3, '편의점 도시락', 4500, 'NONE'),
(4, '촉촉한 물티슈', 2000, 'NONE'),
(4, '종이컵 50개입', 1500, 'NONE');

-- 매장별 재고. 강남점 코카콜라(1)는 재고 1개(품절 임박), 삼각김밥(8)은 0개(품절) 시연용.
INSERT INTO P_STORE_INVENTORY (storeId, productId, inventoryQuantity) VALUES
(1, 1, 1), (1, 2, 40), (1, 3, 25), (1, 4, 30), (1, 5, 50), (1, 6, 35),
(1, 7, 20), (1, 8, 0), (1, 9, 45), (1, 10, 15), (1, 11, 30), (1, 12, 25),
(2, 1, 30), (2, 2, 30), (2, 3, 10), (2, 5, 40), (2, 6, 20), (2, 7, 15), (2, 8, 25),
(3, 1, 20), (3, 2, 25), (3, 5, 30), (3, 9, 18);
