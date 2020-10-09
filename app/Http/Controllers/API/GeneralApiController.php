<?php

namespace App\Http\Controllers\API;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Slider;
use App\FlashDeal;
use App\Product;
use App\Brand;
use App\Category;
use App\SubCategory;
use App\Country;

class GeneralApiController extends Controller
{
    public function banner(){
        $sliders = Slider::where('published', 1)->get();

        return $this->sendResponse($sliders->toArray(), 'Sliders retrieved successfully.');
    }

    public function flash_sales(){
        try {
            $products = [];
            $flash_sales = FlashDeal::where('status', 1)->first();
            $flash_sales_products = $flash_sales->flash_deal_products()->get();
            foreach($flash_sales_products as $key => $flash){
                $products[$key] = Product::find($flash->product_id)->only('id', 'name', 'rating', 'num_of_sale', 'current_stock', 'thumbnail_img', 'purchase_price', 'created_at');
                $products[$key]['discount'] = $flash->discount;
                $products[$key]['discount_type'] = $flash->discount_type;
            }

            return $this->sendResponse($products, 'Flash Sales retrieved successfully.');
        } catch (\Throwable $th) {
            return $this->sendError('Flash Sales Not Found.');
        }

    }

    public function brands(){
        $brands = Brand::all();

        foreach($brands as $key => $brand){
            $brands[$key]['products'] = Product::where('brand_id', $brand->id)->where('published', 1)->get();
        }

        if(count($brands) > 0){
            return $this->sendResponse($brands, 'Brand retrieved successfully.');
        }else{
            return $this->sendError('Brand Not Found.');
        }
    }

    public function categories(){
        $categories = Category::all();

        foreach($categories as $key => $category){
            $categories[$key]['products'] = Product::where('category_id', $category->id)->where('published', 1)->get();
            $categories[$key]['sub_categories'] = SubCategory::where('category_id', $category->id)->get();
            foreach($categories[$key]['sub_categories'] as $skey => $sub){
                $categories[$key]['sub_categories'][$skey]['products'] = Product::where('subcategory_id', $sub->id)->where('published', 1)->get();
            }
        }

        if(count($categories) > 0){
            return $this->sendResponse($categories, 'Categories retrieved successfully.');
        }else{
            return $this->sendError('Categories Not Found.');
        }
    }

    public function sub_categories($category_id){
        $sub_categories = SubCategory::where('category_id', $category_id)->get();

        foreach($sub_categories as $key => $sub_category){
            $sub_categories[$key]['products'] = Product::where('subcategory_id', $sub_category->id)->where('published', 1)->get();
        }

        if(count($sub_categories) > 0){
            return $this->sendResponse($sub_categories, 'Sub Categories retrieved successfully.');
        }else{
            return $this->sendError('Sub Categories Not Found.');
        }
    }

    public function category_product($category_id){
        $products = Product::where('category_id', $category_id)->where('published', 1)->get();

        if(count($products) > 0){
            return $this->sendResponse($products, 'Products by Category retrieved successfully.');
        }else{
            return $this->sendError('Products by Category Not Found.');
        }
    }

    public function sub_category_product($sub_category_id){
        $products = Product::where('subcategory_id', $sub_category_id)->where('published', 1)->get();

        if(count($products) > 0){
            return $this->sendResponse($products, 'Products by Sub Category retrieved successfully.');
        }else{
            return $this->sendError('Products by Sub Category Not Found.');
        }
    }

    public function brand_product($brand_id){
        $products = Product::where('brand_id', $brand_id)->where('published', 1)->get();

        if(count($products) > 0){
            return $this->sendResponse($products, 'Products by Brand retrieved successfully.');
        }else{
            return $this->sendError('Products by Brand Not Found.');
        }
    }

    public function countries(){
        $countries = Country::all();

        if(count($countries) > 0){
            return $this->sendResponse($countries, 'Countries retrieved successfully.');
        }else{
            return $this->sendError('Country Not Found.');
        }
    }

    public function related_products($id){

        $product = Product::findOrFail($id);

        $related = filter_products(\App\Product::where('subcategory_id', $product->subcategory_id)->where('id', '!=', $product->id))->select('id', 'photos', 'name', 'thumbnail_img', 'unit_price', 'purchase_price', 'rating', 'description')->limit(10)->get();

        if(count($related) > 0){
            return $this->sendResponse($related, 'Related Products retrieved successfully.');
        }else{
            return $this->sendError('Related Products Not Found.');
        }
    }

    public function best_selling(){

        $best_selling = filter_products(\App\Product::where('published', 1)->select('id', 'category_id', 'photos', 'featured_img', 'flash_deal_img', 'num_of_sale', 'name', 'thumbnail_img', 'unit_price', 'purchase_price', 'rating', 'description')->orderBy('num_of_sale', 'desc'))->limit(20)->get();

        if(count($best_selling) > 0){
            return $this->sendResponse($best_selling, 'Best Selling Products retrieved successfully.');
        }else{
            return $this->sendError('Products Not Found.');
        }
    }
}
