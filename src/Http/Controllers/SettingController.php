<?php

namespace Component\LaravelPassport\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Component\LaravelPassport\Forms\SettingForm;

class SettingController extends Controller
{
     
    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit(Request $request)
    {
        return view('passport::setting')->withForm(new SettingForm);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request)
    {
        armin_setting([
            '_component_passport_setting' => $request->except([
                '_token'
            ])
        ]);

        return back()->withMsg(1);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        //
    }
}
