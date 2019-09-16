<?php

namespace App\Http\Controllers;

use App\Models\Provider;
use Illuminate\Http\Request;

class ProviderController extends Controller
{
    public function providerView()
    {
        return view('products.provider');
    }

    public function providerList(Request $request)
    {
        $query = Provider::select('*');

        $name = $request->name ?? '';
        if ($name) {
            $query->where('name', 'like', "%$name%");
        }

        $status = $request->status ?? 'all';
        if ($status != 'all') {
            $query->where('status', $status);
        }

        $perPage = intval($request->perPage ?? 25);
        $res = $query->paginate($perPage);

        return response([
            'data' => $res->items(),
            'page' => $res->currentPage(),
            'perPage' => $res->perPage(),
            'total' => $res->total(),
            'lastPage' => $res->lastPage(),
        ]);
    }

    public function addProvider(Request $request)
    {
        $this->validation($request, 'add');

        $provider = new Provider();
        $provider->code = $request->code;
        $provider->name = $request->name;
        $provider->status = 1;
        $provider->maintenance_start = $request->maintenance_start;
        $provider->maintenance_end = $request->maintenance_end;
        $provider->save();
    }

    public function providerEditView($id = 0)
    {
        if ($id != 0) {
            $provider = Provider::find($id);
        }
        return view('products.provider_edit', [
            'provider_id' => $id,
            'provider' => $id != 0 ? $provider : [],
        ]);
    }

    public function editProvider(Request $request)
    {
        $this->validation($request, 'edit');

        $provider = Provider::find($request->id);
        $provider->name = $request->name;
        $provider->maintenance_start = $request->maintenance_start;
        $provider->maintenance_end = $request->maintenance_end;
        $provider->save();
    }

    public function toggleEnabled(Request $request)
    {
        $request->validate([
            'id' => 'required|exists:provider,id',
            'enabled' => 'required|in:0,1',
        ]);

        $provider = Provider::find($request->id);
        $provider->status = $request->enabled;
        $provider->save();
    }

    private function validation($data, $type = 'add')
    {
        $validation = [
            'name' => 'required|string',
            'maintenance_start' => 'nullable|date_format:Y-m-d H:i:s',
            'maintenance_end' => 'nullable|date_format:Y-m-d H:i:s',
        ];

        if ($type == 'edit') {
            $validation['id'] = 'required|exists:provider,id';
        } else if ($type == 'add') {
            $validation['code'] = 'required|string';
        }

        $data->validate($validation);
    }
}