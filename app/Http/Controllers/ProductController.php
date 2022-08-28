<?php

namespace App\Http\Controllers;

use Exception;
use Carbon\Carbon;
use App\Models\Product;
use App\Models\Variant;
use Illuminate\Http\Request;
use App\Models\ProductVariant;
use App\Models\ProductVariantPrice;
use Symfony\Component\HttpFoundation\Response;

class ProductController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Contracts\Foundation\Application|\Illuminate\Contracts\View\Factory|\Illuminate\Http\Response|\Illuminate\View\View
     */
    public function index(Request $request)
    {
        $products = Product::with(['productVariantPrice' => function($q) use($request){
                    return $q->when(($request->price_from && $request->price_to), function($q) use($request) {
                         $q->where('price','>=', $request->price_from)
                        ->where('price','<=', $request->price_to);
                        });
                    }])
                ->applyFilter($request)
                ->paginate(2);

        // return $vars = ProductVariant::select('id', 'variant')->groupBy('variant_id')->groupBy('product_id')->get();
         $variants = Variant::all();

        return view('products.index', compact('products','variants'));
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Contracts\Foundation\Application|\Illuminate\Contracts\View\Factory|\Illuminate\Http\Response|\Illuminate\View\View
     */
    public function create()
    {
        $variants = Variant::all();
        return view('products.create', compact('variants'));
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param \Illuminate\Http\Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function store(Request $request)
    {

        try {
            $product = new Product();
            $product->fill($request->all());
            $product->save();

            if($request->get('product_variant'))
            {
                $product_variants = collect($request->get('product_variant'))->map(function($prodVar) use( $product){

                    return collect($prodVar['tags'])->map(function($tag) use($prodVar, $product){

                        $pVar = new ProductVariant();
                        $pVar->fill([
                            'variant' => $tag,
                            'variant_id' => $prodVar['option'],
                            'product_id' => $product->id,
                            ])->save();
                            return $pVar;
                    });
                });

                // return count($product_variants);

                // if($request->get('product_variant_prices')){

                //     $product_variant_prices = collect($request->get('product_variant_prices'))->map(function($prodVarPrice) use( $product, $product_variants){

                //         $titles = explode('/',$prodVarPrice['title']);
                //         $title_ids = collect($titles)->map(function($title, $key) use($product_variants){
                //             if($title) {
                //                 $variant = collect($product_variants[$key])->where('variant', $title)->first();
                //                 return $variant->id;
                //             }
                //         });

                //         // $pVarPrice = new ProductVariantPrice();
                //         // $pVarPrice->fill([
                //         //     'product_variant_one' => $titles[0]??null,
                //         //     'product_variant_two' => $titles[1]??null,
                //         //     'product_variant_three' => $titles[2]??null,
                //         //     'price' => $prodVarPrice['price'],
                //         //     'stock' => $prodVarPrice['stock'],
                //         //     'product_id' => $product->id
                //         //     ])->save();
                //     });
                //     // return $product_variant_prices;
                // }


            }


            $response = [
                'message' => $product,
                'status' => Response::HTTP_OK
            ];

            return response()->json($response, Response::HTTP_OK);
        } catch (Exception $exception) {
            return response()->json($exception->getMessage(), Response::HTTP_INTERNAL_SERVER_ERROR);
        }


    }


    /**
     * Display the specified resource.
     *
     * @param \App\Models\Product $product
     * @return \Illuminate\Http\Response
     */
    public function show($product)
    {

    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param \App\Models\Product $product
     * @return \Illuminate\Http\Response
     */
    public function edit(Product $product)
    {
        $variants = Variant::all();
        return view('products.edit', compact('variants'));
    }

    /**
     * Update the specified resource in storage.
     *
     * @param \Illuminate\Http\Request $request
     * @param \App\Models\Product $product
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, Product $product)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param \App\Models\Product $product
     * @return \Illuminate\Http\Response
     */
    public function destroy(Product $product)
    {
        //
    }
}
