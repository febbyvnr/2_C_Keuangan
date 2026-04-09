<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdatePenerimaanRequest extends FormRequest
{
    public function authorize(): bool
    {
        // TODO: Hanya Bendahara yang boleh mengubah penerimaan (F83)
        // return $this->user() && $this->user()->hasRole('bendahara');
        return true;
    }

    public function rules(): array
    {
        return [
            'ID_REF_PENERIMAAN'       => ['sometimes', 'integer', 'exists:ref_penerimaan,ID_REF_PENERIMAAN'],
            'ID_REF_DANA'             => ['sometimes', 'integer', 'exists:ref_sumber_dana,ID_REF_DANA'],
            'DESKRIPSI_TR_PENERIMAAN' => ['sometimes', 'string', 'max:100'],
            'TANGGAL_TR_PENERIMAAN'   => ['sometimes', 'date', 'before_or_equal:today'],
            'JUMLAH_TR_PENERIMAAN'    => ['sometimes', 'numeric', 'min:1'],
            'NIP_PENERIMA'            => ['sometimes', 'string', 'exists:mst_karyawan,NIP_KARYAWAN'],
        ];
    }

    public function messages(): array
    {
        return [
            'ID_REF_PENERIMAAN.exists'          => 'Jenis penerimaan tidak ditemukan.',
            'ID_REF_DANA.exists'               => 'Sumber dana tidak ditemukan.',
            'DESKRIPSI_TR_PENERIMAAN.max'       => 'Deskripsi maksimal 100 karakter.',
            'TANGGAL_TR_PENERIMAAN.date'        => 'Format tanggal tidak valid.',
            'TANGGAL_TR_PENERIMAAN.before_or_equal' => 'Tanggal penerimaan tidak boleh di masa depan.',
            'JUMLAH_TR_PENERIMAAN.numeric'      => 'Jumlah penerimaan harus berupa angka.',
            'JUMLAH_TR_PENERIMAAN.min'          => 'Jumlah penerimaan harus lebih dari 0.',
            'NIP_PENERIMA.exists'              => 'Karyawan/penerima tidak ditemukan.',
        ];
    }
}