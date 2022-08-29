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
        $products = Product::with([
            'productVariant' => function($q)  use($request){
                 $q->where('variant', $request->get('variant'));
            },
            'productVariantPrice' => function($q) use($request){
                    return $q->when(($request->get('price_from') && $request->get('price_to')), function($q) use($request) {
                         $q->where('price','>=', $request->get('price_from'))
                        ->where('price','<=', $request->get('price_to'));
                        });
                    },
                ])
                ->applyFilter($request)
                ->latest()
                ->paginate();

          $variants = Variant::with('productVariant')
         ->get()
         ->map(function($item){
            return (object) [
                'id' => $item->id,
                'title' => $item->title,
                'variants' => collect($item->productVariant)->pluck('variant')->unique(),
            ];
         });

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
            // $this->validate($request, [
            //     'product_name' => 'required',
            //     'product_sku' => 'required|unique:products.sku'
            // ]);

            $product = new Product();
            $product->fill($request->all());
            $product->save();

            //Product Variants
            if($request->get('product_variant'))
            {
                $product_variants = $product->product_variants =  collect($request->get('product_variant'))->map(function($prodVar) use( $product){

                    return collect($prodVar['tags'])->map(function($tag) use($prodVar, $product){

                        $pVar = new ProductVariant();
                        $pVar->fill([
                            'variant' => $tag,
                            'variant_id' => $prodVar['option'],
                            'product_id' => $product->id,
                            ])->save();
                            return $pVar;
                    })->pluck('variant','id');
                });

                // return ($product_variants);

                if($request->get('product_variant_prices')){

                    $product_variant_prices = $product->product_variant_prices =  collect($request->get('product_variant_prices'))->map(function($prodVarPrice) use( $product, $product_variants){

                        $titles = explode('/',$prodVarPrice['title']);
                        $product_variant_one = $this->getVariantIdByTitle($product_variants, $titles, 0 );
                        $product_variant_two = $this->getVariantIdByTitle($product_variants, $titles, 1 );
                        $product_variant_three = $this->getVariantIdByTitle($product_variants, $titles, 2 );

                        $pVarPrice = new ProductVariantPrice();
                        $pVarPrice->fill([
                            'product_variant_one' => $product_variant_one??null,
                            'product_variant_two' => $product_variant_two??null,
                            'product_variant_three' => $product_variant_three??null,
                            'price' => $prodVarPrice['price'],
                            'stock' => $prodVarPrice['stock'],
                            'product_id' => $product->id
                        ])->save();
                    });
                }


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

    //Search Variant id
    public function getVariantIdByTitle($product_variants, $titles, $index )
    {
        if(isset($product_variants[$index]) && isset($titles[$index]))
        {
            return collect($product_variants[$index])->search($titles[$index]);
        }else{
            return null;
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
        $product->load('productVariant.variantType');
        // return $product;

        $productVariantList = $product->productVariantList = collect($product->productVariant)
            ->groupBy('variantType.title')
            ->map(function($item){

               $tags = collect($item)->map(function($val){
                    return [
                        'id' => $val->id,
                        'variant' => $val->variant
                    ];
                })->pluck('variant','id');

                return [
                    'option' => $item[0]->variantType->id,
                    'tags' => $tags,
                    'tagsWithID' => $tags,
                ];

            });
        return view('products.edit', compact('product','productVariantList','variants'));
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
