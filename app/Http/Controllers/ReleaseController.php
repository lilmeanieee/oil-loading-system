<?php

namespace App\Http\Controllers;

use App\Support\DemoData;
use App\Support\Role;
use Illuminate\Http\Request;

class ReleaseController extends Controller
{
    public function index()
    {
        abort_unless(Role::can('documents.view'), 403);

        return view('documents.index', ['documents' => DemoData::documents()]);
    }

    public function create(int $atl)
    {
        abort_unless(Role::can('documents.issue'), 403);

        $atl = DemoData::findAtl($atl) ?? abort(404);
        abort_unless(in_array($atl['status'], ['Approved', 'Loading', 'Completed']), 404);

        // One line per product, using the names printed on the Delivery Receipt and Withdrawal Certificate.
        $lines = collect($atl['items'])->groupBy('product')->map(function ($rows, $name) {
            $p = DemoData::productByAtl($name);

            return ['name' => $p['doc'] ?? $name, 'liters' => $rows->sum('volume'), 'tax' => $p['tax_note'] ?? ''];
        })->values()->all();

        return view('releases.create', [
            'atl'     => $atl,
            'lines'   => $lines,
            'next'    => DemoData::nextNumbers(),
            'used'    => collect(DemoData::documents())->pluck('no')->all(),
            'signers' => DemoData::signers(),
        ]);
    }

    public function store(Request $request)
    {
        abort_unless(Role::can('documents.issue'), 403);

        // TODO (backend): reject duplicate DR / WCP numbers, save both documents together, mark them "Issued", build the .docx files.
        return redirect()->route('documents.index')->with('status', 'Delivery receipt and withdrawal certificate issued. (Demo only.)');
    }
}
