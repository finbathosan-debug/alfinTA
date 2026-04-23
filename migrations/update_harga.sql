-- Hapus kolom harga_beli_alfin dan ubah harga_jual_alfin menjadi harga_alfin
ALTER TABLE produk_alfin DROP COLUMN harga_beli_alfin;
ALTER TABLE produk_alfin CHANGE harga_jual_alfin harga_alfin INT(35) NOT NULL;