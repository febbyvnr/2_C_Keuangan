<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StorePenerimaanRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true; 
    }

    public function rules(): array
    {
        return [
            'ID_REF_PENERIMAAN'       => ['required', 'exists:ref_penerimaan,ID_REF_PENERIMAAN'],
            'ID_REF_DANA'             => ['required', 'exists:ref_sumber_dana,ID_REF_DANA'],
            'DESKRIPSI_TR_PENERIMAAN' => ['required', 'string', 'max:100'],
            'TANGGAL_TR_PENERIMAAN'   => ['required', 'date', 'before_or_equal:today'],
            'JUMLAH_TR_PENERIMAAN'    => ['required', 'numeric', 'min:1'],
            'NIP_PENERIMA'            => ['required', 'exists:mst_karyawan,NIP_KARYAWAN'],
        ];
    }
}