<?php

namespace App\Http\Controllers\admin;

use App\Http\Controllers\Controller;
use App\Models\Brand;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class BrandController extends Controller
{
    public function index(Request $request){
        $brands = Brand::latest('id');

        if (!empty($request ->get('keyword'))) {
            $brands = $brands->where('name', 'like', '%'.$request ->get('keyword').'%');
        }

        $brands = $brands->paginate(10);
        return view('admin.brands.list', compact('brands'));
    }

    public function create(){
        return view('admin.brands.create');
    }

    public function store(Request $request){
        $validator = Validator::make($request->all(),[
            'name'=>'required',
            'slug'=>'required|unique:brands',
            'status'=>'required',
            ]);
        
        
            if($validator->passes()){
            
                $brands = new Brand();
                $brands->name = $request->name;
                $brands->slug = $request->slug;
                $brands->status = $request->status;
                $brands->save();
    
                $request->flash('success', 'Brands added successfully');   
    
                return response()->json([
                    'status' => true,
                    'message' => 'Brands added successfully'
                   ]);
    
            } else {
               
                // $request->flash('success', 'Brands added successfully');   
    
                return response()->json([
                 'status' => false,
                 'errors' => $validator->errors()
                ]);
            }
    }

    public function edit($id, Request $request){
        $brand = Brand::find($id);

        if (empty($brand)) {
              return redirect()->route('brands.index');
        }
        $data['brand'] = $brand;
        // return view('admin.brands.edit', compact('brand')); on peut l'utiliser soit le suivants qui a le $data, si on ulise le compact on met ne doit pas mettre le code de $data
        return view('admin.brands.edit', $data);

    }

    public function update($id, Request $request){
        $brand = Brand::find($id);

         if (empty($brand)) {
            $request->flash('error','Record not found');
            return response([
                'status' => false,
                'notFound' => true
            ]);
         }

        $validator = Validator::make($request->all(), [
            'name'=>'required',
            'slug'=>'required|unique:brands',
            'status'=>'required',
        ]);

        if($validator->passes()){
            
            $brand->name = $request->name;
            $brand->slug = $request->slug;
            $brand->status = $request->status;
            $brand->save();

            $request->flash('success', 'Brands updated successfully');   

            return response([
                'status' => true,
                'message' => 'Brands updated successfully'
               ]);

        } else {
           
            // $request->flash('success', 'Brands added successfully');   

            return response([
             'status' => false,
             'errors' => $validator->errors()
            ]);
        }
    }

    public function destroy($id, Request $request){
        $brand = Brand::find($id);

         if (empty($brand)) {
            $request->flash('error','Record not found');
            return response([
                'status' => false,
                'notFound' => true
            ]);
        //    return redirect()->route('sub-categories.index');
         }

        $brand->delete();

        $request->flash('success', 'Brands deleted successfully');   

        return response()->json([
            'status' => true,
            'mssage' => 'Brands deleted successfully',
        ]);
    }
    
}

