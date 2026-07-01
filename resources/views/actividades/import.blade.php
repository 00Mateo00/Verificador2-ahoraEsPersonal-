@extends('layouts.app')

@section('title', 'Importar Actividades - Intranet CAJBIOBIO')

@section('breadcrumbs')
<a href="#">Actividades</a>
<span class="separator">‣</span>
<span>Importar Planilla</span>
@endsection



@section('content')
<livewire:actividades.import-actividades-form />
@endsection