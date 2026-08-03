di dalam jenis ujian nanti, terdapat beberapa lagi sub Jenis Ujian.
misalnya :
Jenis Ujian : SKB
          - Sub Jenis Ujian : Hukum Materil
          - Jumlah Jawaban Pilihan ganda : 4 atau 5
          - sistem penilaian : benar-salah / tiap jawaban ada poin (seperti nilai TKP), yang paling benar bernilai 5, hingga 4,3,2,1
          - nilai jawaban benar (bila benar-salah) : angka (default : 5)

	Contoh isian tabelnya nanti
Id | jenisUjian   | namaSubJenisUjian | sistemPenilaian  |  jumlahJawabanPilihanGanda  | nilaibenar
-------------------------------------------------------------------------------------------------------------------
1  | 1                 | hukum materil           | benar-salah        | 5                                               | 5
2  | 1                 | Psikotest                   | tiap jawaban ada poin |    5                                   | -




    - sekarang, di dalam subjenis ujian, ada lagi sub indikator di dalamnya
	- misalnya , sub jenis hukum materil,
		Sub indikatornya : Perdata (nanti hanya masukkan nama sub indikator).
	Contoh isian tabelnya

Id | jenisUjian | SubJenisUjian | Nama Sub Jenis Ujian
-----------------------------------------------------------------------
1  | 1               | 1                      | Perdata

Jadi secara garis besar, untuk jenis ujian itu seperti ini

jenis ujian
	Sub ujian
		Indikator.


Ini nanti akan berguna ketika memasukkan soal, akan dikelompokkan berdasarkan jenis ujian, sub_ujian, maupun indikator.
