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
  `kode_skpd` varchar(255) DEFAULT NULL,
  `nama_skpd` varchar(255) DEFAULT NULL,
  `kode_sub_skpd` varchar(255) DEFAULT NULL,
  `nama_sub_skpd` varchar(255) DEFAULT NULL,
  `nama_lokasi` text DEFAULT NULL,
  `kode_lokasi` varchar(255) DEFAULT NULL,
  `kode_aset` varchar(255) DEFAULT NULL,
  `nama_aset` text DEFAULT NULL,
  `tanggal_perolehan` date DEFAULT NULL,
  `tanggal_pengadaan` date DEFAULT NULL,
  `kondisi` text DEFAULT NULL,
  `no_register` int(50) DEFAULT NULL,
  `asal_usul` text DEFAULT NULL,
  `luas_tanah` varchar(255) DEFAULT NULL,
  `alamat` text DEFAULT NULL,
  `keterangan` text DEFAULT NULL,
  `satuan` varchar(50) DEFAULT NULL,
  `klasifikasi` text DEFAULT NULL,
  `tanggal_sertifikat` date DEFAULT NULL,
  `no_sertifikat` varchar(50) DEFAULT NULL,
  `status_sertifikat` text DEFAULT NULL,
  `umur_ekonomis` int(50) DEFAULT NULL,
  `masa_pakai` varchar(255) DEFAULT NULL,
  `nilai_perolehan` int(50) DEFAULT NULL,
  `jumlah_barang` int(50) DEFAULT NULL,
  `created_at` datetime DEFAULT current_timestamp,
  `active` tinyint(4) DEFAULT 1,
 PRIMARY KEY(id)
);

CREATE TABLE `data_laporan_kib_b` (
  `id` int(11) NOT NULL auto_increment,
  `kode_skpd` varchar(255) DEFAULT NULL,
  `nama_skpd` varchar(255) DEFAULT NULL,
  `kode_sub_skpd` varchar(255) DEFAULT NULL,
  `nama_sub_skpd` varchar(255) DEFAULT NULL,
  `kode_unit` varchar(255) DEFAULT NULL,
  `nama_unit` text DEFAULT NULL,
  `nama_lokasi` text DEFAULT NULL,
  `kode_lokasi` varchar(255) DEFAULT NULL,
  `kode_aset` varchar(255) DEFAULT NULL,
  `nama_aset` text DEFAULT NULL,
  `tanggal_perolehan` date DEFAULT NULL,
  `tanggal_pengadaan` date DEFAULT NULL,
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
  `umur_ekonomis` int(50) DEFAULT NULL,
  `masa_pakai` varchar(255) DEFAULT NULL,
  `nilai_perolehan` int(50) DEFAULT NULL,
  `nilai_aset` int(50) DEFAULT NULL,
  `nilai_dasar_perhitungan` int(50) DEFAULT NULL,
  `nilai_penyusutan_per_tahun` int(50) DEFAULT NULL,
  `nilai_penyusutan` int(50) DEFAULT NULL,
  `beban_penyusutan` varchar(255) DEFAULT NULL,
  `akumulasi_penyusutan` varchar(255) DEFAULT NULL,
  `nilai_buku` varchar(255) DEFAULT NULL,
  `jumlah_barang` int(50) DEFAULT NULL,
  `created_at` datetime DEFAULT current_timestamp,
  `active` tinyint(4) DEFAULT 1,
 PRIMARY KEY(id)
);

CREATE TABLE `data_laporan_kib_c` (
  `id` int(11) NOT NULL auto_increment,
  `kode_skpd` varchar(255) DEFAULT NULL,
  `nama_skpd` varchar(255) DEFAULT NULL,
  `kode_sub_skpd` varchar(255) DEFAULT NULL,
  `nama_sub_skpd` varchar(255) DEFAULT NULL,
  `nama_unit` text DEFAULT NULL,
  `kode_unit` varchar(255) DEFAULT NULL,
  `nama_lokasi` text DEFAULT NULL,
  `kode_lokasi` varchar(255) DEFAULT NULL,
  `kode_aset` varchar(255) DEFAULT NULL,
  `nama_aset` text DEFAULT NULL,
  `tanggal_perolehan` date DEFAULT NULL,
  `tanggal_pengadaan` date DEFAULT NULL,
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
  `umur_ekonomis` int(50) DEFAULT NULL,
  `masa_pakai` varchar(255) DEFAULT NULL,
  `bulan_terpakai` text DEFAULT NULL,
  `total_bulan_terpakai` text DEFAULT NULL,
  `penyusutan_ke` int(50) DEFAULT NULL,
  `penyusutan_per_tanggal` int(50) DEFAULT NULL,
  `nilai_dasar_perhitungan` int(50) DEFAULT NULL,
  `nilai_penyusutan_per_tahun` int(50) DEFAULT NULL,
  `nilai_aset` int(50) DEFAULT NULL,
  `beban_penyusutan` varchar(255) DEFAULT NULL,
  `akumulasi_penyusutan` varchar(255) DEFAULT NULL,
  `nilai_perolehan` int(50) DEFAULT NULL,
  `nilai_buku` varchar(255) DEFAULT NULL,
  `jumlah_barang` int(50) DEFAULT NULL,
  `created_at` datetime DEFAULT current_timestamp,
  `active` tinyint(4) DEFAULT 1,
 PRIMARY KEY(id)
);

CREATE TABLE `data_laporan_kib_d` (
  `id` int(11) NOT NULL auto_increment,
  `kode_skpd` varchar(255) DEFAULT NULL,
  `nama_skpd` varchar(255) DEFAULT NULL,
  `kode_sub_skpd` varchar(255) DEFAULT NULL,
  `nama_sub_skpd` varchar(255) DEFAULT NULL,
  `nama_unit` text DEFAULT NULL,
  `kode_unit` varchar(255) DEFAULT NULL,
  `nama_lokasi` text DEFAULT NULL,
  `kode_lokasi` varchar(255) DEFAULT NULL,
  `kode_aset` varchar(255) DEFAULT NULL,
  `nama_aset` text DEFAULT NULL,
  `tanggal_perolehan` date DEFAULT NULL,
  `tanggal_pengadaan` date DEFAULT NULL,
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
  `umur_ekonomis` int(50) DEFAULT NULL,
  `masa_pakai` varchar(255) DEFAULT NULL,
  `penyusutan_ke` int(50) DEFAULT NULL,
  `penyusutan_per_tanggal` int(50) DEFAULT NULL,
  `nilai_perolehan` int(50) DEFAULT NULL,
  `nilai_aset` int(50) DEFAULT NULL,
  `nilai_dasar_perhitungan` int(50) DEFAULT NULL,
  `nilai_penyusutan_per_tahun` int(50) DEFAULT NULL,
  `beban_penyusutan` varchar(255) DEFAULT NULL,
  `akumulasi_penyusutan` varchar(255) DEFAULT NULL,
  `nilai_buku` varchar(255) DEFAULT NULL,
  `jumlah_barang` int(50) DEFAULT NULL,
  `created_at` datetime DEFAULT current_timestamp,
  `active` tinyint(4) DEFAULT 1,
 PRIMARY KEY(id)
);

CREATE TABLE `data_laporan_kib_e` (
  `id` int(11) NOT NULL auto_increment,
  `kode_skpd` varchar(255) DEFAULT NULL,
  `nama_skpd` varchar(255) DEFAULT NULL,
  `kode_sub_skpd` varchar(255) DEFAULT NULL,
  `nama_sub_skpd` varchar(255) DEFAULT NULL,
  `nama_unit` text DEFAULT NULL,
  `kode_unit` varchar(255) DEFAULT NULL,
  `nama_lokasi` text DEFAULT NULL,
  `kode_lokasi` varchar(255) DEFAULT NULL,
  `kode_aset` varchar(255) DEFAULT NULL,
  `nama_aset` text DEFAULT NULL,
  `tanggal_perolehan` date DEFAULT NULL,
  `tanggal_pengadaan` date DEFAULT NULL,
  `asal_usul` text DEFAULT NULL,
  `alamat` text DEFAULT NULL,
  `umur_ekonomis` int(50) DEFAULT NULL,
  `keterangan` text DEFAULT NULL,
  `kondisi` text DEFAULT NULL,
  `no_register` varchar(50) DEFAULT NULL,
  `nilai_perolehan` int(50) DEFAULT NULL,
  `buku_pencipta` text DEFAULT NULL,
  `spesifikasi` text DEFAULT NULL,
  `asal_daerah` text DEFAULT NULL,
  `pencipta` text DEFAULT NULL,
  `bahan` text DEFAULT NULL,
  `jenis_hewan` text DEFAULT NULL,
  `ukuran` varchar(50) DEFAULT NULL,
  `jumlah` varchar(50) DEFAULT NULL,
  `satuan` varchar(50) DEFAULT NULL,
  `nilai_aset` int(50) DEFAULT NULL,
  `nilai_dasar_perhitungan` int(50) DEFAULT NULL,
  `nilai_penyusutan_per_tahun` int(50) DEFAULT NULL,
  `akumulasi_penyusutan` varchar(255) DEFAULT NULL,
  `klasifikasi` text DEFAULT NULL,
  `nilai_buku` varchar(255) DEFAULT NULL,
  `beban_penyusutan` varchar(255) DEFAULT NULL,
  `masa_pakai` varchar(255) DEFAULT NULL,
  `jumlah_barang` int(50) DEFAULT NULL,
  `created_at` datetime DEFAULT current_timestamp,
  `active` tinyint(4) DEFAULT 1,
 PRIMARY KEY(id)
);