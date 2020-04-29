@extends('dashboard::layouts.main')

@section('title')
	@lang("licence::title.auth-setting")
@stop 

@section('breadcrumbs')
	@component('dashboard::components.breadcrumbs')
		@slot('title')@lang("licence::title.auth-setting")@endslot 
	@endcomponent 
@stop

@section('main')
{!! $form->open(['method' => 'post', 'route' => 'passport-setting.edit']) !!}
<div class="columns">
	<div class="twelve-columns">
		@var($_buttons = [
				'only' => ['save'], 
		])  
		@include('backend.includes.action-button', compact('_buttons')) 
	</div>
	<div class="six-columns">
		{!! $form !!} 
	</div>
	<div class="six-columns">
		<b class="red">هشدار :</b>
		<br> 
		<h4>مقدار <b class="red" style="font-size: 18px; font-weight: bolder;">#</b> در مسیج با کد جایگزین خواهد شد. در صورت نداشتن به انتهای پیام افزوده خواهد شد.</h4>
		<h4>مقدار منحصربفرد مربوط به نرم افزار شما.</h4>
	</div>
</div> 
{!! $form->close() !!}
@stop