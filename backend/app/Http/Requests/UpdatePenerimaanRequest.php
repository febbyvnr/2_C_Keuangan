<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdatePenerimaanRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true; 
    }

    public function rules(): array
    {
        return [
            'ID_REF_PENERIMAAN'       => ['sometimes', 'exists:ref_penerimaan,ID_REF_PENERIMAAN'],
            'ID_REF_DANA'             => ['sometimes', 'exists:ref_sumber_dana,ID_REF_DANA'],
            'DESKRIPSI_TR_PENERIMAAN' => ['sometimes', 'string', 'max:100'],
            'TANGGAL_TR_PENERIMAAN'   => ['sometimes', 'date', 'before_or_equal:today'],
            'JUMLAH_TR_PENERIMAAN'    => ['sometimes', 'numeric', 'min:1'],
            'NIP_PENERIMA'            => ['sometimes', 'exists:mst_karyawan,NIP_KARYAWAN'],
        ];
    }
}