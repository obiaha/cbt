<?php
defined('BASEPATH') or exit('No direct script access allowed');

class ScormController extends CI_Controller
{

  public function __construct()
  {
    parent::__construct();
    $this->load->library('scorm_export');
  }

  public function export_scorm()
  {
    $course_title = 'Judul Kursus Anda';

    // Contoh data JSON soal
    $data_soal_json = '
        {
            "course": "Contoh Kursus SCORM",
            "questions": [
              {
                "question": "Apa ibu kota Indonesia?",
                "options": [
                  "Jakarta",
                  "Bandung",
                  "Surabaya",
                  "Medan"
                ],
                "correct_option": "Jakarta"
              },
              {
                "question": "Berapakah hasil dari 4 + 5?",
                "options": [
                  "6",
                  "7",
                  "8",
                  "9"
                ],
                "correct_option": "9"
              },
              {
                "question": "Siapakah penemu bola lampu?",
                "options": [
                  "Thomas Edison",
                  "Nikola Tesla",
                  "Albert Einstein",
                  "Isaac Newton"
                ],
                "correct_option": "Thomas Edison"
              }
            ]
          }
          
        ';

    $package_name = 'materi';

    $scorm_package = $this->scorm_export->create_scorm_package($course_title, $data_soal_json, $package_name);

    if ($scorm_package) {
      // Jika Anda ingin mengirim paket sebagai respons download:
      // header('Content-Type: application/zip');
      // header('Content-Disposition: attachment; filename="' . basename($scorm_package) . '"');
      // readfile($scorm_package);

      // Jika Anda ingin menyimpan paket di server:
      $destination_dir = FCPATH . 'assets/export_scorm/';
      if (!file_exists($destination_dir)) {
        mkdir($destination_dir, 0777, true);
      }

      $destination_path = $destination_dir . basename($scorm_package);
      if (rename($scorm_package, $destination_path)) {
        // Hapus paket dari direktori sementara (tidak perlu lagi setelah diunduh atau disimpan)
        if (file_exists($scorm_package)) {
          unlink($scorm_package);
        }

        echo "Paket SCORM berhasil dibuat dan disimpan.";
      } else {
        echo "Gagal memindahkan paket SCORM.";
      }
    } else {
      echo "Gagal membuat paket SCORM.";
    }
  }
}
