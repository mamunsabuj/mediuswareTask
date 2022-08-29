@extends('layouts.app')

@section('content')

    <div class="d-sm-flex align-items-center justify-content-between mb-4">
        <h1 class="h3 mb-0 text-gray-800">Products</h1>
    </div>


    <div class="card">
        <form action="{{ route('product.index') }}" method="get" class="card-header">
            <div class="form-row justify-content-between">
                <div class="col-md-2">
                    <input type="text" name="title" placeholder="Product Title" class="form-control" value="{{ request()->get('title')??null }}">
                </div>
                <div class="col-md-2">
                    <select name="variant" id="" class="form-control">
                        <option value="">Select A Variant</option>
                        @foreach($variants as $variant)
                            <optgroup label="{{ $variant->title }}">
                                @forelse($variant->variants as $var)
                                <option value="{{ $var }}" {{ request()->get('variant')==$var?'selected':'' }}>{{ $var }}</option>
                                @empty
                                @endforelse
                            </optgroup>
                        @endforeach
                    </select>
                </div>

                <div class="col-md-3">
                    <div class="input-group">
                        <div class="input-group-prepend">
                            <span class="input-group-text">Price Range</span>
                        </div>
                        <input type="text" name="price_from" aria-label="First name" placeholder="From" class="form-control"  value="{{ request()->get('price_from')??null }}">
                        <input type="text" name="price_to" aria-label="Last name" placeholder="To" class="form-control" value="{{ request()->get('price_to')??null }}">
                    </div>
                </div>
                <div class="col-md-2">
                    <input type="date" name="date" placeholder="Date" class="form-control"  value="{{ request()->get('date')??null }}">
                </div>
                <div class="col-md-1">
                    <button type="submit" class="btn btn-primary float-right"><i class="fa fa-search"></i></button>
                </div>
            </div>
        </form>

        <div class="card-body">
            <div class="table-response">
                <table class="table">
                    <thead>
                    <tr>
                        <th>#</th>
                        <th>Title</th>
                        <th>Description</th>
                        <th>Variant</th>
                        <th width="150px">Action</th>
                    </tr>
                    </thead>

                    <tbody>
                    @forelse ($products as $product)
                        <tr>
                            <td>{{ $loop->index+1 }}</td>
                            <td>{{ $product->title }} <br> Created at : {{ date('d-M-Y', strtotime($product->created_at)) }}</td>
                            <td>{{ $product->description }}</td>
                            <td>
                                <dl class="row mb-0" style="height: 80px; overflow: hidden" id="variant">

                                    @forelse($product->productVariantPrice as $price)
                                    <dt class="col-sm-3 pb-0">
                                        @php

                                        $variants = ($price->product_variant_one ? $price->productVariant->variant??null:'').
                                            ($price->product_variant_two ? '/'.$price->productVariantTwo->variant??null:'').
                                            ($price->product_variant_three ? '/'.$price->productVariantThree->variant??null:'')
                                        @endphp
                                        {{  $variants }}
                                    </dt>
                                    <dd class="col-sm-9">
                                        <dl class="row mb-0">
                                            <dt class="col-sm-4 pb-0">Price : {{ number_format($price->price,2) }}</dt>
                                            <dd class="col-sm-8 pb-0">InStock : {{ number_format($price->stock,2) }}</dd>
                                        </dl>
                                    </dd>
                                    @empty
                                    <dt class="col-sm-3 pb-0">
                                        No variant
                                    </dt>
                                    @endforelse
                                </dl>
                                <button onclick="$('#variant').toggleClass('h-auto')" class="btn btn-sm btn-link">Show more</button>
                            </td>
                            <td>
                                <div class="btn-group btn-group-sm">
                                    <a href="{{ route('product.edit', 1) }}" class="btn btn-success">Edit</a>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5">No Data Found!</td>
                        </tr>
                    @endforelse


                    </tbody>

                </table>
            </div>

        </div>

        <div class="card-footer">
            <div class="row justify-content-between">
                <div class="col-md-12">
                    {{ $products->withQueryString()->links() }}
                </div>
            </div>
        </div>
    </div>

@endsection
