CREATE TABLE `data_mapping_rek_a` (
  `id` int(11) NOT NULL auto_increment,
  `kode_rekening_spbmd` varchar(20) NOT NULL,
  `uraian_rekening_spbmd` text DEFAULT NULL,
  `kode_rekening_ebmd` varchar(20) DEFAULT NULL,
  `uraian_rekening_ebmd` text DEFAULT NULL,
  `update_at` datetime NOT NULL,
  `active` tinyint(4) DEFAULT 1,
  PRIMARY KEY  (id)
);

CREATE TABLE `data_mapping_rek_b` (
  `id` int(11) NOT NULL auto_increment,
  `kode_rekening_spbmd` varchar(20) NOT NULL,
  `uraian_rekening_spbmd` text DEFAULT NULL,
  `kode_rekening_ebmd` varchar(20) DEFAULT NULL,
  `uraian_rekening_ebmd` text DEFAULT NULL,
  `update_at` datetime NOT NULL,
  `active` tinyint(4) DEFAULT 1,
  PRIMARY KEY  (id)
);

CREATE TABLE `data_mapping_rek_c` (
  `id` int(11) NOT NULL auto_increment,
  `kode_rekening_spbmd` varchar(20) NOT NULL,
  `uraian_rekening_spbmd` text DEFAULT NULL,
  `kode_rekening_ebmd` varchar(20) DEFAULT NULL,
  `uraian_rekening_ebmd` text DEFAULT NULL,
  `update_at` datetime NOT NULL,
  `active` tinyint(4) DEFAULT 1,
  PRIMARY KEY  (id)
);

CREATE TABLE `data_mapping_rek_d` (
  `id` int(11) NOT NULL auto_increment,
  `kode_rekening_spbmd` varchar(20) NOT NULL,
  `uraian_rekening_spbmd` text DEFAULT NULL,
  `kode_rekening_ebmd` varchar(20) DEFAULT NULL,
  `uraian_rekening_ebmd` text DEFAULT NULL,
  `update_at` datetime NOT NULL,
  `active` tinyint(4) DEFAULT 1,
  PRIMARY KEY  (id)
);

CREATE TABLE `data_mapping_rek_e` (
  `id` int(11) NOT NULL auto_increment,
  `kode_rekening_spbmd` varchar(20) NOT NULL,
  `uraian_rekening_spbmd` text DEFAULT NULL,
  `kode_rekening_ebmd` varchar(20) DEFAULT NULL,
  `uraian_rekening_ebmd` text DEFAULT NULL,
  `update_at` datetime NOT NULL,
  `active` tinyint(4) DEFAULT 1,
  PRIMARY KEY  (id)
);

CREATE TABLE `data_mapping_rek_f` (
  `id` int(11) NOT NULL auto_increment,
  `kode_rekening_spbmd` varchar(20) NOT NULL,
  `uraian_rekening_spbmd` text DEFAULT NULL,
  `kode_rekening_ebmd` varchar(20) DEFAULT NULL,
  `uraian_rekening_ebmd` text DEFAULT NULL,
  `update_at` datetime NOT NULL,
  `active` tinyint(4) DEFAULT 1,
  PRIMARY KEY  (id)
);

CREATE TABLE `data_laporan_kib_a` (
  `id` int(11) NOT NULL auto_increment,
  `id_tanah` double(20, 0) DEFAULT NULL,
  `kode_skpd` varchar(255) DEFAULT NULL,
  `nama_skpd` varchar(255) DEFAULT NULL,
  `kode_lokasi` varchar(255) DEFAULT NULL,
  `kode_lokasi_mapping` varchar(255) DEFAULT NULL,
  `nama_lokasi` text DEFAULT NULL,
  `kode_barang` varchar(255) DEFAULT NULL,
  `jenis_barang` text DEFAULT NULL,
  `kode_aset` varchar(255) DEFAULT NULL,
  `nama_aset` text DEFAULT NULL,
  `tanggal_perolehan` varchar(20) DEFAULT NULL,
  `tanggal_pengadaan` varchar(20) DEFAULT NULL,
  `kondisi` text DEFAULT NULL,
  `no_register` varchar(50) DEFAULT NULL,
  `asal_usul` text DEFAULT NULL,
  `luas_tanah` varchar(255) DEFAULT NULL,
  `alamat` text DEFAULT NULL,
  `keterangan` text DEFAULT NULL,
  `satuan` varchar(50) DEFAULT NULL,
  `klasifikasi` text DEFAULT NULL,
  `tanggal_sertifikat` varchar(20) DEFAULT NULL,
  `no_sertifikat` varchar(50) DEFAULT NULL,
  `status_sertifikat` text DEFAULT NULL,
  `umur_ekonomis` int(11) DEFAULT NULL,
  `masa_pakai` varchar(255) DEFAULT NULL,
  `nilai_perolehan` double(20, 0) DEFAULT NULL,
  `jumlah_barang` int(11) DEFAULT NULL,
  `guna` text DEFAULT NULL,
  `register_serti` varchar(50) DEFAULT NULL,
  `created_at` datetime DEFAULT current_timestamp,
  `active` tinyint(4) DEFAULT 1,
 PRIMARY KEY(id)
);

CREATE TABLE `data_laporan_kib_b` (
  `id` int(11) NOT NULL auto_increment,
  `id_mesin` double(20, 0) DEFAULT NULL,
  `kode_skpd` varchar(255) DEFAULT NULL,
  `nama_skpd` varchar(255) DEFAULT NULL,
  `kode_unit` varchar(255) DEFAULT NULL,
  `nama_unit` text DEFAULT NULL,
  `kode_lokasi` varchar(255) DEFAULT NULL,
  `kode_lokasi_mapping` varchar(255) DEFAULT NULL,
  `nama_lokasi` text DEFAULT NULL,
  `kode_barang` varchar(255) DEFAULT NULL,
  `jenis_barang` text DEFAULT NULL,
  `kode_aset` varchar(255) DEFAULT NULL,
  `nama_aset` text DEFAULT NULL,
  `tanggal_perolehan` varchar(20) DEFAULT NULL,
  `tanggal_pengadaan` varchar(20) DEFAULT NULL,
  `kondisi` text DEFAULT NULL,
  `no_register` varchar(50) DEFAULT NULL,
  `asal_usul` text DEFAULT NULL,
  `alamat` text DEFAULT NULL,
  `pengguna` text DEFAULT NULL,
  `keterangan` text DEFAULT NULL,
  `merk` text DEFAULT NULL,
  `ukuran` varchar(20) DEFAULT NULL,
  `bahan` text DEFAULT NULL,
  `warna` text DEFAULT NULL,
  `no_pabrik` text DEFAULT NULL,
  `no_mesin` text DEFAULT NULL,
  `no_kerangka` text DEFAULT NULL,
  `no_polisi` text DEFAULT NULL,
  `no_bpkb` text DEFAULT NULL,
  `bahan_bakar` text DEFAULT NULL,
  `satuan` varchar(50) DEFAULT NULL,
  `no_bapp` varchar(50) DEFAULT NULL,
  `klasifikasi` text DEFAULT NULL,
  `umur_ekonomis` int(11) DEFAULT NULL,
  `masa_pakai` varchar(255) DEFAULT NULL,
  `nilai_perolehan` double(20, 0) DEFAULT NULL,
  `nilai_aset` double(20, 0) DEFAULT NULL,
  `nilai_dasar_perhitungan` double(20, 0) DEFAULT NULL,
  `nilai_penyusutan_per_tahun` double(20, 0) DEFAULT NULL,
  `nilai_penyusutan` double(20, 0) DEFAULT NULL,
  `beban_penyusutan` double(20, 0) DEFAULT NULL,
  `akumulasi_penyusutan` double(20, 0) DEFAULT NULL,
  `nilai_buku` double(20, 0) DEFAULT NULL,
  `jumlah_barang` int(11) DEFAULT NULL,
  `created_at` datetime DEFAULT current_timestamp,
  `active` tinyint(4) DEFAULT 1,
 PRIMARY KEY(id)
);

CREATE TABLE `data_laporan_kib_c` (
  `id` int(11) NOT NULL auto_increment,
  `kode_skpd` varchar(255) DEFAULT NULL,
  `id_gedung` double(20, 0) DEFAULT NULL,
  `nama_skpd` varchar(255) DEFAULT NULL,
  `nama_unit` text DEFAULT NULL,
  `kode_unit` varchar(255) DEFAULT NULL,
  `kode_lokasi` varchar(255) DEFAULT NULL,
  `kode_lokasi_mapping` varchar(255) DEFAULT NULL,
  `nama_lokasi` text DEFAULT NULL,
  `kode_barang` varchar(255) DEFAULT NULL,
  `jenis_barang` text DEFAULT NULL,
  `kode_aset` varchar(255) DEFAULT NULL,
  `nama_aset` text DEFAULT NULL,
  `tanggal_perolehan` varchar(20) DEFAULT NULL,
  `tanggal_pengadaan` varchar(20) DEFAULT NULL,
  `kondisi` text DEFAULT NULL,
  `no_register` varchar(50) DEFAULT NULL,
  `asal_usul` text DEFAULT NULL,
  `keterangan` text DEFAULT NULL,
  `tingkat` varchar(20) DEFAULT NULL,
  `beton` varchar(20) DEFAULT NULL,
  `luas_bangunan` varchar(20) DEFAULT NULL,
  `alamat` text DEFAULT NULL,
  `luas_tanah` varchar(20) DEFAULT NULL,
  `kode_tanah` varchar(50) DEFAULT NULL,
  `satuan` varchar(50) DEFAULT NULL,
  `klasifikasi` text DEFAULT NULL,
  `umur_ekonomis` int(11) DEFAULT NULL,
  `masa_pakai` varchar(255) DEFAULT NULL,
  `bulan_terpakai` text DEFAULT NULL,
  `total_bulan_terpakai` text DEFAULT NULL,
  `penyusutan_ke` double(20, 0) DEFAULT NULL,
  `penyusutan_per_tanggal` double(20, 0) DEFAULT NULL,
  `nilai_dasar_perhitungan` double(20, 0) DEFAULT NULL,
  `nilai_penyusutan_per_tahun` double(20, 0) DEFAULT NULL,
  `nilai_aset` double(20, 0) DEFAULT NULL,
  `beban_penyusutan` double(20, 0) DEFAULT NULL,
  `akumulasi_penyusutan` double(20, 0) DEFAULT NULL,
  `nilai_perolehan` double(20, 0) DEFAULT NULL,
  `nilai_buku` double(20, 0) DEFAULT NULL,
  `jumlah_barang` int(11) DEFAULT NULL,
  `created_at` datetime DEFAULT current_timestamp,
  `active` tinyint(4) DEFAULT 1,
 PRIMARY KEY(id)
);

CREATE TABLE `data_laporan_kib_d` (
  `id` int(11) NOT NULL auto_increment,
  `id_jalan_irigasi` double(20, 0) DEFAULT NULL,
  `kode_skpd` varchar(255) DEFAULT NULL,
  `nama_skpd` varchar(255) DEFAULT NULL,
  `nama_unit` text DEFAULT NULL,
  `kode_unit` varchar(255) DEFAULT NULL,
  `kode_lokasi` varchar(255) DEFAULT NULL,
  `kode_lokasi_mapping` varchar(255) DEFAULT NULL,
  `nama_lokasi` text DEFAULT NULL,
  `kode_barang` varchar(255) DEFAULT NULL,
  `jenis_barang` text DEFAULT NULL,
  `kode_aset` varchar(255) DEFAULT NULL,
  `nama_aset` text DEFAULT NULL,
  `tanggal_perolehan` varchar(20) DEFAULT NULL,
  `tanggal_pengadaan` varchar(20) DEFAULT NULL,
  `hak` text DEFAULT NULL,
  `no_register` varchar(50) DEFAULT NULL,
  `asal_usul` text DEFAULT NULL,
  `keterangan` text DEFAULT NULL,
  `bahan_kontruksi` varchar(50) DEFAULT NULL,
  `panjang` varchar(50) DEFAULT NULL,
  `lebar` varchar(50) DEFAULT NULL,
  `luas` varchar(20) DEFAULT NULL,
  `alamat` text DEFAULT NULL,
  `satuan` varchar(50) DEFAULT NULL,
  `klasifikasi` text DEFAULT NULL,
  `umur_ekonomis` int(11) DEFAULT NULL,
  `masa_pakai` varchar(255) DEFAULT NULL,
  `penyusutan_ke` varchar(255) DEFAULT NULL,
  `penyusutan_per_tanggal` varchar(255) DEFAULT NULL,
  `nilai_perolehan` double(20, 0) DEFAULT NULL,
  `nilai_aset` double(20, 0) DEFAULT NULL,
  `nilai_dasar_perhitungan` double(20, 0) DEFAULT NULL,
  `nilai_penyusutan_per_tahun` double(20, 0) DEFAULT NULL,
  `beban_penyusutan` double(20, 0) DEFAULT NULL,
  `akumulasi_penyusutan` double(20, 0) DEFAULT NULL,
  `nilai_buku` double(20, 0) DEFAULT NULL,
  `jumlah_barang` int(11) DEFAULT NULL,
  `created_at` datetime DEFAULT current_timestamp,
  `active` tinyint(4) DEFAULT 1,
 PRIMARY KEY(id)
);

CREATE TABLE `data_laporan_kib_e` (
  `id` int(11) NOT NULL auto_increment,
  `id_aset_tetap` double(20, 0) DEFAULT NULL,
  `kode_skpd` varchar(255) DEFAULT NULL,
  `nama_skpd` varchar(255) DEFAULT NULL,
  `nama_unit` text DEFAULT NULL,
  `kode_unit` varchar(255) DEFAULT NULL,
  `kode_lokasi` varchar(255) DEFAULT NULL,
  `kode_lokasi_mapping` varchar(255) DEFAULT NULL,
  `nama_lokasi` text DEFAULT NULL,
  `kode_barang` varchar(255) DEFAULT NULL,
  `jenis_barang` text DEFAULT NULL,
  `kode_aset` varchar(255) DEFAULT NULL,
  `nama_aset` text DEFAULT NULL,
  `tanggal_perolehan` varchar(20) DEFAULT NULL,
  `tanggal_pengadaan` varchar(20) DEFAULT NULL,
  `asal_usul` text DEFAULT NULL,
  `alamat` text DEFAULT NULL,
  `umur_ekonomis` int(11) DEFAULT NULL,
  `keterangan` text DEFAULT NULL,
  `kondisi` text DEFAULT NULL,
  `no_register` varchar(50) DEFAULT NULL,
  `nilai_perolehan` double(20, 0) DEFAULT NULL,
  `buku_pencipta` text DEFAULT NULL,
  `spesifikasi` text DEFAULT NULL,
  `asal_daerah` text DEFAULT NULL,
  `pencipta` text DEFAULT NULL,
  `bahan` text DEFAULT NULL,
  `jenis_hewan` text DEFAULT NULL,
  `ukuran` varchar(50) DEFAULT NULL,
  `jumlah` varchar(50) DEFAULT NULL,
  `satuan` varchar(50) DEFAULT NULL,
  `nilai_aset` double(20, 0) DEFAULT NULL,
  `nilai_dasar_perhitungan` double(20, 0) DEFAULT NULL,
  `nilai_penyusutan_per_tahun` double(20, 0) DEFAULT NULL,
  `akumulasi_penyusutan` double(20, 0) DEFAULT NULL,
  `klasifikasi` text DEFAULT NULL,
  `nilai_buku` double(20, 0) DEFAULT NULL,
  `beban_penyusutan` double(20, 0) DEFAULT NULL,
  `masa_pakai` varchar(255) DEFAULT NULL,
  `jumlah_barang` int(11) DEFAULT NULL,
  `created_at` datetime DEFAULT current_timestamp,
  `active` tinyint(4) DEFAULT 1,
 PRIMARY KEY(id)
);


CREATE TABLE `penyusutan_mesin_2023` (
  `id` int(11) NOT NULL auto_increment,
  `kd_prop` text DEFAULT NULL,
  `kd_kab` text DEFAULT NULL,
  `kd_satker` text DEFAULT NULL,
  `kd_Unit` text DEFAULT NULL,
  `kd_sub_unit` text DEFAULT NULL,
  `NAMA_satker` text DEFAULT NULL,
  `NAMA_unit` text DEFAULT NULL,
  `NAMA_sub_unit` text DEFAULT NULL,
  `jab_pengguna` text DEFAULT NULL,
  `nama_pengguna` text DEFAULT NULL,
  `nip_pengguna` text DEFAULT NULL,
  `jab_pengurus` text DEFAULT NULL,
  `nama_pengurus` text DEFAULT NULL,
  `nip_pengurus` text DEFAULT NULL,
  `id_mesin` text DEFAULT NULL,
  `milik` text DEFAULT NULL,
  `kd_lokasi` text DEFAULT NULL,
  `kd_barang` text DEFAULT NULL,
  `jenis_barang` text DEFAULT NULL,
  `register` text DEFAULT NULL,
  `merk` text DEFAULT NULL,
  `ukuran` text DEFAULT NULL,
  `bahan` text DEFAULT NULL,
  `thn_beli` text DEFAULT NULL,
  `no_pabrik` text DEFAULT NULL,
  `no_rangka` text DEFAULT NULL,
  `no_mesin` text DEFAULT NULL,
  `no_polisi` text DEFAULT NULL,
  `no_bpkb` text DEFAULT NULL,
  `asal` text DEFAULT NULL,
  `harga` text DEFAULT NULL,
  `keterangan` text DEFAULT NULL,
  `jumlah` text DEFAULT NULL,
  `tgl_pengadaan` text DEFAULT NULL,
  `tgl_penyusutan` text DEFAULT NULL,
  `nilai_buku_yll` text DEFAULT NULL,
  `harga_disusutkan` text DEFAULT NULL,
  `sisa_ue_yll` text DEFAULT NULL,
  `penyusutan_yll` text DEFAULT NULL,
  `akumulasi_penyusutan_bulat_yll` text DEFAULT NULL,
  `tgl_proses` text DEFAULT NULL,
  `sisa_ue_termasuk_skr` text DEFAULT NULL,
  `penyusutan_skr` text DEFAULT NULL,
  `nilai_buku_skr` text DEFAULT NULL,
  `sisa_ue_stl_sst` text DEFAULT NULL,
  `katagori` text DEFAULT NULL,
  `penyusutan_per_tahun` text DEFAULT NULL,
  `created_at` datetime DEFAULT current_timestamp,
  `active` tinyint(4) DEFAULT 1,
   PRIMARY KEY(id)
);

CREATE TABLE `penyusutan_gedung_2023` (
  `kd_prop` char(3) NOT NULL,
  `kd_kab` char(3) NOT NULL,
  `kd_satker` char(3) NOT NULL,
  `kd_Unit` char(3) NOT NULL,
  `kd_sub_unit` char(3) NOT NULL,
  `NAMA_satker` varchar(255) DEFAULT NULL,
  `NAMA_unit` varchar(255) DEFAULT NULL,
  `NAMA_sub_unit` varchar(150) DEFAULT NULL,
  `jab_pengguna` varchar(255) DEFAULT NULL,
  `nama_pengguna` varchar(55) DEFAULT NULL,
  `nip_pengguna` varchar(45) DEFAULT NULL,
  `jab_pengurus` varchar(255) DEFAULT NULL,
  `nama_pengurus` varchar(100) DEFAULT NULL,
  `nip_pengurus` varchar(45) DEFAULT NULL,
  `id_gedung` bigint(20) unsigned NOT NULL,
  `milik` varchar(45) DEFAULT NULL,
  `kd_lokasi` varchar(45) DEFAULT NULL,
  `kd_barang` varchar(100) DEFAULT NULL,
  `jenis_barang` varchar(200) DEFAULT NULL,
  `register` varchar(45) DEFAULT NULL,
  `kondisi` varchar(45) DEFAULT NULL,
  `kontruksi_tingkat` varchar(20) DEFAULT NULL,
  `kontruksi_beton` varchar(20) DEFAULT NULL,
  `luas_lantai` varchar(45) DEFAULT NULL,
  `alamat` varchar(200) DEFAULT NULL,
  `tgl_dok_gedung` varchar(45) DEFAULT NULL,
  `no_dok_gedung` varchar(45) DEFAULT NULL,
  `luas_tanah` varchar(45) DEFAULT NULL,
  `status_tanah` varchar(45) DEFAULT NULL,
  `no_kode_tanah` varchar(45) DEFAULT NULL,
  `asal` varchar(45) DEFAULT NULL,
  `harga` bigint(10) DEFAULT NULL,
  `keterangan` varchar(200) DEFAULT NULL,
  `jumlah` int(10) unsigned DEFAULT '0',
  `HARGA_AWAL` bigint(10) unsigned DEFAULT NULL,
  `tgl_penyusutan` varchar(20) DEFAULT NULL,
  `nilai_buku_yll` bigint(15) unsigned DEFAULT NULL,
  `akumulasi_kapitalisasi` bigint(10) unsigned DEFAULT NULL,
  `harga_disusutkan` bigint(10) unsigned DEFAULT NULL,
  `sisa_ue_yll` int(10) unsigned DEFAULT NULL,
  `tambah_ue` int(10) unsigned DEFAULT NULL,
  `penyusutan_yll` bigint(10) DEFAULT NULL,
  `akumulasi_penyusutan_bulat_yll` bigint(10) DEFAULT NULL,
  `tgl_proses` varchar(20) DEFAULT NULL,
  `sisa_ue_termasuk_skr` int(10) unsigned DEFAULT NULL,
  `penyusutan_skr` bigint(10) unsigned DEFAULT NULL,
  `nilai_buku_skr` bigint(15) DEFAULT NULL,
  `sisa_ue_stl_sst` int(10) unsigned DEFAULT NULL,
  `katagori` int(10) unsigned DEFAULT '0',
  `penyusutan_per_tahun` int(10) unsigned DEFAULT '0',
  PRIMARY KEY (`id_gedung`)
);

CREATE TABLE `penyusutan_jalan_irigasi_2023` (
  `kd_prop` char(3) NOT NULL,
  `kd_kab` char(3) NOT NULL,
  `kd_satker` char(3) NOT NULL,
  `kd_Unit` char(3) NOT NULL,
  `kd_sub_unit` char(3) NOT NULL,
  `NAMA_satker` varchar(255) DEFAULT NULL,
  `NAMA_unit` varchar(255) DEFAULT NULL,
  `NAMA_sub_unit` varchar(150) DEFAULT NULL,
  `jab_pengguna` varchar(255) DEFAULT NULL,
  `nama_pengguna` varchar(55) DEFAULT NULL,
  `nip_pengguna` varchar(45) DEFAULT NULL,
  `jab_pengurus` varchar(255) DEFAULT NULL,
  `nama_pengurus` varchar(100) DEFAULT NULL,
  `nip_pengurus` varchar(45) DEFAULT NULL,
  `id_jalan_irigasi` bigint(20) unsigned NOT NULL,
  `milik` varchar(45) DEFAULT NULL,
  `kd_lokasi` varchar(45) DEFAULT NULL,
  `kd_barang` varchar(45) DEFAULT NULL,
  `jenis_barang` varchar(145) DEFAULT NULL,
  `register` varchar(45) DEFAULT NULL,
  `kontruksi` varchar(45) DEFAULT NULL,
  `panjang` varchar(10) DEFAULT NULL,
  `lebar` varchar(10) DEFAULT NULL,
  `luas` varchar(10) DEFAULT NULL,
  `letak` varchar(200) DEFAULT NULL,
  `dok_tanggal` varchar(20) DEFAULT NULL,
  `dok_nomor` varchar(45) DEFAULT NULL,
  `status_tanah` varchar(150) DEFAULT NULL,
  `no_kode_tanah` varchar(45) DEFAULT NULL,
  `asal` varchar(45) DEFAULT NULL,
  `harga` bigint(20) DEFAULT NULL,
  `kondisi` varchar(45) DEFAULT NULL,
  `keterangan` varchar(245) DEFAULT NULL,
  `jumlah` int(10) unsigned NOT NULL DEFAULT '0',
  `HARGA_AWAL` bigint(10) unsigned DEFAULT NULL,
  `tgl_penyusutan` varchar(20) DEFAULT NULL,
  `nilai_buku_yll` bigint(15) unsigned DEFAULT NULL,
  `akumulasi_kapitalisasi` bigint(10) unsigned DEFAULT NULL,
  `harga_disusutkan` bigint(10) unsigned DEFAULT NULL,
  `sisa_ue_yll` int(10) unsigned DEFAULT NULL,
  `tambah_ue` int(10) unsigned DEFAULT NULL,
  `penyusutan_yll` bigint(10) DEFAULT NULL,
  `akumulasi_penyusutan_bulat_yll` bigint(10) DEFAULT NULL,
  `tgl_proses` varchar(20) DEFAULT NULL,
  `sisa_ue_termasuk_skr` int(10) unsigned DEFAULT NULL,
  `penyusutan_skr` bigint(10) unsigned DEFAULT NULL,
  `nilai_buku_skr` bigint(15) unsigned DEFAULT NULL,
  `sisa_ue_stl_sst` int(10) unsigned DEFAULT NULL,
  `katagori` int(10) unsigned DEFAULT '0',
  `penyusutan_per_tahun` bigint(20) unsigned DEFAULT '0',
  PRIMARY KEY (`id_jalan_irigasi`)
);

CREATE TABLE `data_laporan_aset_lain` (
  `id` int(11) NOT NULL auto_increment,
  `kode_skpd` varchar(255) DEFAULT NULL,
  `nama_skpd` varchar(255) DEFAULT NULL,
  `kode_unit` varchar(255) DEFAULT NULL,
  `nama_unit` text DEFAULT NULL,
  `kode_lokasi` varchar(255) DEFAULT NULL,
  `kode_lokasi_mapping` varchar(255) DEFAULT NULL,
  `nama_lokasi` text DEFAULT NULL,
  `kode_barang` varchar(255) DEFAULT NULL,
  `jenis_barang` text DEFAULT NULL,
  `kode_aset` varchar(255) DEFAULT NULL,
  `nama_aset` text DEFAULT NULL,
  `tanggal_perolehan` varchar(255) DEFAULT NULL,
  `tanggal_pengadaan` varchar(255) DEFAULT NULL,
  `kondisi` text DEFAULT NULL,
  `no_register` varchar(50) DEFAULT NULL,
  `asal_usul` text DEFAULT NULL,
  `alamat` text DEFAULT NULL,
  `pengguna` text DEFAULT NULL,
  `keterangan` text DEFAULT NULL,
  `merk` text DEFAULT NULL,
  `ukuran` varchar(20) DEFAULT NULL,
  `bahan` text DEFAULT NULL,
  `warna` text DEFAULT NULL,
  `no_pabrik` text DEFAULT NULL,
  `no_mesin` text DEFAULT NULL,
  `no_kerangka` text DEFAULT NULL,
  `no_polisi` text DEFAULT NULL,
  `no_bpkb` text DEFAULT NULL,
  `bahan_bakar` text DEFAULT NULL,
  `satuan` varchar(50) DEFAULT NULL,
  `no_bapp` varchar(50) DEFAULT NULL,
  `klasifikasi` text DEFAULT NULL,
  `umur_ekonomis` int(11) DEFAULT NULL,
  `masa_pakai` varchar(255) DEFAULT NULL,
  `nilai_perolehan` double(20, 0) DEFAULT NULL,
  `nilai_aset` double(20, 0) DEFAULT NULL,
  `nilai_dasar_perhitungan` double(20, 0) DEFAULT NULL,
  `nilai_penyusutan_per_tahun` double(20, 0) DEFAULT NULL,
  `nilai_penyusutan` double(20, 0) DEFAULT NULL,
  `beban_penyusutan` double(20, 0) DEFAULT NULL,
  `akumulasi_penyusutan` double(20, 0) DEFAULT NULL,
  `nilai_buku` double(20, 0) DEFAULT NULL,
  `jumlah_barang` int(11) DEFAULT NULL,
  `id_mesin` double(20, 0) DEFAULT NULL,
  `created_at` datetime DEFAULT current_timestamp,
  `active` tinyint(4) DEFAULT 1,
 PRIMARY KEY(id)
);