-- --------------------------------------------------------
-- Host:                         127.0.0.1
-- Server version:               8.0.30 - MySQL Community Server - GPL
-- Server OS:                    Win64
-- HeidiSQL Version:             12.1.0.6537
-- --------------------------------------------------------

/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET NAMES utf8 */;
/*!50503 SET NAMES utf8mb4 */;
/*!40103 SET @OLD_TIME_ZONE=@@TIME_ZONE */;
/*!40103 SET TIME_ZONE='+00:00' */;
/*!40014 SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS=0 */;
/*!40101 SET @OLD_SQL_MODE=@@SQL_MODE, SQL_MODE='NO_AUTO_VALUE_ON_ZERO' */;
/*!40111 SET @OLD_SQL_NOTES=@@SQL_NOTES, SQL_NOTES=0 */;

-- Dumping structure for table eco_protect_store.category
CREATE TABLE IF NOT EXISTS `category` (
  `id` int NOT NULL AUTO_INCREMENT,
  `name` varchar(100) NOT NULL,
  `description` text,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=4 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- Dumping data for table eco_protect_store.category: ~3 rows (approximately)
INSERT INTO `category` (`id`, `name`, `description`) VALUES
	(1, 'Đồ gia dụng', 'Các sản phẩm thay thế nhựa dùng một lần'),
	(2, 'Thời trang bền vững', 'Quần áo từ sợi tự nhiên'),
	(3, 'Làm sạch xanh', 'Chất tẩy rửa sinh học');

-- Dumping structure for table eco_protect_store.product
CREATE TABLE IF NOT EXISTS `product` (
  `id` int NOT NULL AUTO_INCREMENT,
  `name` varchar(100) NOT NULL,
  `description` text,
  `price` decimal(10,2) NOT NULL,
  `image` varchar(255) DEFAULT NULL,
  `category_id` int DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `category_id` (`category_id`),
  CONSTRAINT `product_ibfk_1` FOREIGN KEY (`category_id`) REFERENCES `category` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=10 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- Dumping data for table eco_protect_store.product: ~9 rows (approximately)
INSERT INTO `product` (`id`, `name`, `description`, `price`, `image`, `category_id`, `created_at`) VALUES
	(1, 'Hộp bã mía đựng thức ăn', 'Làm từ 100% sợi bã mía tự nhiên. Tự phân hủy hoàn toàn trong lòng đất sau 6 tuần. An toàn khi sử dụng trong lò vi sóng và tủ lạnh, không sinh độc tố.', 2000.00, 'uploads/1779259172_bamia.jfif', 1, '2026-05-20 06:39:32'),
	(2, 'Màng bọc thực phẩm từ sáp ong', 'Thay thế hoàn toàn màng bọc nilon dùng một lần. Thành phần gồm vải cotton organic, sáp ong tự nhiên, dầu jojoba và nhựa cây. Có thể rửa sạch bằng nước lạnh và tái sử dụng lên đến 1 năm.', 10000.00, 'uploads/1779259227_sapong.webp', 1, '2026-05-20 06:40:27'),
	(3, 'Khăn lau đa năng từ sợi xơ mướp', 'Xơ mướp tự nhiên được xử lý thủ công, cắt lát mỏng tiện lợi. Giúp tạo bọt tốt, rửa sạch chén bát hoặc lau chùi nhà bếp mà không làm trầy xước bề mặt vật dụng.', 10000.00, 'uploads/1779259273_somuop.jfif', 1, '2026-05-20 06:41:13'),
	(4, 'Viên nén gỗ nén làm sạch không khí', 'Sản xuất từ mùn cưa và gỗ vụn tái chế. Kết hợp tinh dầu thông giúp hút ẩm, khử mùi hôi trong tủ quần áo, tủ giày một cách tự nhiên và an toàn.', 3000.00, 'uploads/1779259316_viennen.jfif', 3, '2026-05-20 06:41:56'),
	(5, 'Áo sơ mi nam vải sợi sen', 'Chất liệu độc đáo kết hợp giữa sợi cellulose chiết xuất từ lá, gốc sen và cotton organic. Vải mềm mịn, có khả năng chống tia UV tự nhiên, thấm hút mồ hôi vượt trội và tự phân hủy sinh học.', 300000.00, 'uploads/1779259372_sen.jfif', 2, '2026-05-20 06:42:52'),
	(6, 'Túi xách thời trang từ da xương rồng', 'Sản phẩm thời trang thuần chay cao cấp. Bề mặt túi làm từ chất liệu tổng hợp sinh học từ cây xương rồng Desserto, hoàn toàn không chứa chất độc hại, thân thiện với động vật và môi trường.', 2000000.00, 'uploads/1779259416_tuisach.jfif', 2, '2026-05-20 06:43:36'),
	(7, 'Nước giặt sinh học tinh dầu bưởi', 'Công nghệ enzyme bóc tách vết bẩn tiên tiến. Chiết xuất bồ hòn kết hợp hương tinh dầu vỏ bưởi tự nhiên. Nước thải sau khi giặt không gây chết vi sinh vật, an toàn cho nguồn nước đất.', 230000.00, 'uploads/1779259476_buoi.webp', 3, '2026-05-20 06:44:36'),
	(8, 'Nước lau sàn Enzyme cam dứa', 'Lên men tự nhiên từ vỏ cam và vỏ dứa (phế phẩm nông nghiệp). Làm sạch bóng sàn nhà, xua đuổi côn trùng (kiến, muỗi, gián) mà không để lại màng hóa chất dính chân.', 125000.00, 'uploads/1779259513_lausan.jfif', 3, '2026-05-20 06:45:13'),
	(9, 'Nước rửa tay tạo bọt thảo mộc', 'Chiết xuất từ dịch truyền lá trầu không và trà xanh giúp kháng khuẩn tự nhiên. Thiết kế chai thủy tinh có thể mang ra cửa hàng để refill (lấp đầy lại) khi dùng hết.', 67000.00, 'uploads/1779259559_ruatay.jfif', 3, '2026-05-20 06:45:59');

/*!40103 SET TIME_ZONE=IFNULL(@OLD_TIME_ZONE, 'system') */;
/*!40101 SET SQL_MODE=IFNULL(@OLD_SQL_MODE, '') */;
/*!40014 SET FOREIGN_KEY_CHECKS=IFNULL(@OLD_FOREIGN_KEY_CHECKS, 1) */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40111 SET SQL_NOTES=IFNULL(@OLD_SQL_NOTES, 1) */;
