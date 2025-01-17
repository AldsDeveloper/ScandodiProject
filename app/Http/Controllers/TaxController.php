<?php

namespace App\Http\Controllers;

use App\Models\Item;
use App\Models\Tax;
use Illuminate\Http\Request;

class TaxController extends Controller
{
    public function index()
    {
        $user = auth()->user();
        $data['taxes'] = Tax::where('user_id', $user->id)->get();
        return view('tax.index', $data);
    }

    public function create()
    {
        return view('tax.create');
    }

    public function store(Request $request)
    {
        $request->validate([
            'title' => 'required|max:191',
            'amount' => 'required|numeric|gt:-1',
            'type' => 'required|in:flat,percentage',
            'status' => 'required|in:active,inactive',
        ]);

        $user = auth()->user();
        $user->taxes()->create($request->all());
        return redirect()->route('tax.index')->with('success',trans('layout.message.tax_success_msg'));

    }

    public function edit(Tax $tax)
    {
        $data['tax']=$tax;
        return view('tax.edit',$data);
    }

    public function update(Tax $tax,Request $request)
    {
        $request->validate([
            'title' => 'required|max:191',
            'amount' => 'required|numeric|gt:-1',
            'type' => 'required|in:flat,percentage',
            'status' => 'required|in:active,inactive',
        ]);
        unset($request['_token']);
        unset($request['_method']);
        $user = auth()->user();
        $user->taxes()->where('id',$tax->id)->update($request->all());
        return redirect()->back()->with('success',trans('layout.message.tax_update_msg'));

    }

    public function destroy(Tax $tax)
    {
        $user = auth()->user();
        if ($tax->user_id != $user->id){
            abort(404);
        }
        $item = Item::where('tax_id', $tax->id)->first();
        if($item){
            return redirect()->back()->withErrors(['failed'=>trans('layout.tax_already_used_can_not_delete_at_this_moment')]);
        }
        $tax->delete();
        return redirect()->back()->with('success',trans('layout.message.tax_delete_msg'));
    }
}
