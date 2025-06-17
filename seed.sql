USE hotel_reservations;

-- Insert sample rooms
INSERT INTO hotel_rooms (room_number, capacity, base_price) VALUES
-- Single rooms
('101', 1, 50.00),
('102', 1, 50.00),
('103', 1, 55.00),

-- Double rooms
('201', 2, 75.00),
('202', 2, 75.00),
('203', 2, 80.00),

-- Triple rooms
('301', 3, 100.00),
('302', 3, 100.00),
('303', 3, 110.00),

-- Family rooms
('401', 4, 130.00),
('402', 4, 130.00),
('403', 4, 140.00),

-- Suite rooms
('501', 2, 150.00),
('502', 3, 180.00),
('503', 4, 200.00); 