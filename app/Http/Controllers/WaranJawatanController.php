<?php

namespace App\Http\Controllers;

use App\Models\WaranJawatan;
use App\Models\Waran;
use Illuminate\Http\Request;

class WaranJawatanController extends Controller
{
    public function store(Request $request, Waran $waran)
    {
        $request->validate([
            'aktiviti_id' => 'required',
            'butiran'     => 'required',
            'ptj_id'      => 'required',
        ]);

        $waran->waranJawatan()->create([
            'aktiviti_id'     => $request->aktiviti_id,
            'butiran'         => $request->butiran,
            'jawatan_ids'     => $request->jawatan_ids ?? [],
            'gred_ids'        => $request->gred_ids ?? [],
            'ptj_id'          => $request->ptj_id,
            'bahagian_id'     => $request->bahagian_id ?: null,
            'unit_id'         => $request->unit_id ?: null,
            'subunit_id'      => $request->subunit_id ?: null,
            'pegawai_id'      => $request->pegawai_id ?: null,
            'is_kup'          => $request->boolean('is_kup'),
            'catatan_jawatan' => $request->catatan_jawatan,
            'status'          => 'active',
        ]);

        return redirect()->route('waran.show', $waran)->with('success', 'Jawatan berjaya ditambah!');
    }

    public function update(Request $request, WaranJawatan $waranJawatan)
    {
        $request->validate([
            'aktiviti_id' => 'required',
            'butiran'     => 'required',
            'ptj_id'      => 'required',
        ]);

        $waranJawatan->update([
            'aktiviti_id'     => $request->aktiviti_id,
            'butiran'         => $request->butiran,
            'jawatan_ids'     => $request->jawatan_ids ?? [],
            'gred_ids'        => $request->gred_ids ?? [],
            'ptj_id'          => $request->ptj_id,
            'bahagian_id'     => $request->bahagian_id ?: null,
            'unit_id'         => $request->unit_id ?: null,
            'subunit_id'      => $request->subunit_id ?: null,
            'pegawai_id'      => $request->pegawai_id ?: null,
            'is_kup'          => $request->boolean('is_kup'),
            'catatan_jawatan' => $request->catatan_jawatan,
        ]);

        return redirect()->route('waran.show', $waranJawatan->waran_id)->with('success', 'Jawatan berjaya dikemaskini!');
    }

    public function destroy(WaranJawatan $waranJawatan)
    {
        $waranId = $waranJawatan->waran_id;
        $waranJawatan->delete();
        return redirect()->route('waran.show', $waranId)->with('success', 'Jawatan berjaya dipadam!');
    }
}