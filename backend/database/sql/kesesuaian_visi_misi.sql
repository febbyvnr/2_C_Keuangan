-- ============================================================
-- Kesesuaian Visi Misi pada Evaluasi Penjaminan Mutu
-- ============================================================

-- 1. Buat tabel ref_visi_misi
CREATE TABLE `ref_visi_misi` (
  `ID_VISI_MISI` int(11) NOT NULL AUTO_INCREMENT,
  `TIPE` ENUM('Visi', 'Misi') NOT NULL,
  `DESKRIPSI` varchar(255) NOT NULL,
  `IS_ACTIVE` tinyint(1) DEFAULT 1,
  PRIMARY KEY (`ID_VISI_MISI`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- 2. Tambah kolom ke tr_pm + FK
ALTER TABLE `tr_pm`
  ADD COLUMN `ID_VISI_MISI` int(11) DEFAULT NULL AFTER `DESKRIPSI_TR_PM`,
  ADD COLUMN `TINGKAT_KESESUAIAN` ENUM('Sesuai', 'Kurang Sesuai', 'Tidak Sesuai') DEFAULT NULL AFTER `ID_VISI_MISI`,
  ADD CONSTRAINT `FK_TR_PM_VISI_MISI` FOREIGN KEY (`ID_VISI_MISI`) REFERENCES `ref_visi_misi`(`ID_VISI_MISI`) ON DELETE SET NULL;
