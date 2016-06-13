@extends('platformAdmin::layouts.app')

@section('title_name') Create OAuth Scope @endsection

@section('body_content')

<div class="container" style="margin-top: 3%">
    <div class="row">
        <h1>Create OAuth Permission Scope</h1>
    </div>

    @include('platformAdmin::includes.errors')


    {!! Form::open([
        'method' => 'POST',
        'route' => ['platform.admin.scopes.store'],
    ]) !!}

    <div class="row">

        <div class="six columns">
            {!! Form::label('id', 'Scope ID') !!}
            {!! Form::text('id', null, ['class' => 'u-full-width']) !!}            
            <br><br>
            {!! Form::label('label', 'Label') !!}
            {!! Form::text('label', null, ['class' => 'u-full-width']) !!}            
            <br><br>            
            {!! Form::label('description', 'Description') !!}
            {!! Form::textarea('description', null, ['class' => 'u-full-width', ]) !!}      
            <br><br>
            {!! Form::label('notice_level', 'Notice Level') !!}
            <select id="notice_label" name="notice_level">
                @for($i = 0; $i < 4; $i++)
                    <option value="{{ $i }}">{{ $i }}</option>
                @endfor;
            </select>                       
        </div>

        <div class="six columns">
        </div>

        <div class="six columns">
        </div>
    </div>


    <div class="row" style="margin-top: 3%;">
        <div class="three columns">
            {!! Form::submit('Create', ['class' => 'button-primary u-full-width']) !!}
        </div>
        <div class="six columns">&nbsp;</div>
        <div class="three columns">
            <a class="button u-full-width" href="{{ route('platform.admin.scopes.index') }}">Cancel</a>
        </div>
    </div>

    {!! Form::close() !!}

</div>

@endsection

