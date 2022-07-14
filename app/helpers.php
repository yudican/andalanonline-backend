<?php

// define permissions
if (!function_exists('permissionLists')) {
  function permissionLists()
  {
    $permissions = [
      'create' => 'Create',
      // 'read' => 'Read',
      'update' => 'Update',
      'delete' => 'Delete',
    ];
    return $permissions;
  }
}

if (!function_exists('statusPengajuan')) {
  function statusPengajuan($status)
  {
    switch ($status) {
      case 1:
        return ['msg' => 'Disetujui', 'color' => '#00b894'];
      case 2:
        return ['msg' => 'Ditolak', 'color' => '#d63031'];

      default:
        return ['msg' => 'Menunggu Konfirmasi', 'color' => '#f9ca24'];
    }
  }
}

if (!function_exists('statusAbsen')) {
  function statusAbsen($status)
  {
    switch ($status) {
      case 'cuti':
        return 2;
      case 'sakit':
        return 3;
      case 'izin':
        return 4;

      default:
        return 1;
    }
  }
}
