<?php

namespace App\Http\Controllers\admin;

use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Session;
use App\Models\Category;
use App\Models\TempImage;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Validator;
// use Intervention\Image\Facades\Image;
use Intervention\Image\Image;
// use Image;
// use Intervention\Image\Image;


class CategoryController extends Controller
{
    public function index(Request $request){
        $category = Category::latest();
        if (!empty($request ->get('keyword'))) {
            $category = $category->where('name', 'like', '%'.$request ->get('keyword').'%');
        }

        $category = $category->paginate(10);
        return view('admin.category.list', compact('category'));
    }


    public function create(){
       return view('admin.category.create');
    }



    public function store(Request $request){
        $validator = Validator::make($request->all(),[
            'name'=>'required',
            'slug'=>'required|unique:categories',
            ]);

            if($validator->passes()){

               $category = new Category();
               $category->name = $request->name;
               $category->slug = $request->slug;
               $category->status = $request->status;
               $category->save();

            //    Enregistrement de l'image ici
                if (!empty($request->image_id)) {
                   $tempImage = TempImage::find($request->image_id);
                   $extArray = explode('.', $tempImage->name);
                   $ext = last($extArray);
                   
                   $newImageName = $category->id.'.'.$ext;
                   $sPath = public_path().'/temp/'.$tempImage->name;
                   $dPath = public_path().'/uploads/category/'.$newImageName;
                   File::copy($sPath, $dPath);

                //    generate Image Thumbnail
                //    $dPath = public_path().'/uploads/category/thumb/'.$newImageName;
                //    $img = Image::make($sPath);
                //    $img->resize(450, 600); 
                //    $img->save($dPath);

                   $category->image = $newImageName;
                   $category->save();
                }
               

               $request->flash('success', 'Category added successfully');   

               return response()->json([
                'status' => true,
                'message' => 'Category added successfully'
            ]);

            } else{
                return response()->json([
                    'status' => false,
                    'errors' => $validator->errors(),
                ]);
            }   
    }



    public function edit($categoryId, Request $request){
        $category = Category::find($categoryId);

        if (empty($categoryId)) {
              return redirect()->route('categories.index');
        }
        
        return view('admin.category.edit', compact('category'));
    }



    public function update($categoryId, Request $request){

        $category = Category::find($categoryId);

        if (empty($category)) {
            $request->flash('error', 'Category not found');   
            
            return response()->json([
                'status' => false,
                'notFound' => true,
                'message' => 'Category not found',
            ]);
        }
        
        $validator = Validator::make($request->all(),[
            'name'=>'required',
            'slug'=>'required|unique:categories,slug,'.$category->id.'id',
            ]);

            if($validator->passes()){

               $category->name = $request->name;
               $category->slug = $request->slug;
               $category->status = $request->status;
               $category->save();

               $oldImage =  $category->image;

            //    Enregistrement de l'image ici
                if (!empty($request->image_id)) {
                   $tempImage = TempImage::find($request->image_id);
                   $extArray = explode('.', $tempImage->name);
                   $ext = last($extArray);
                   
                   $newImageName = $category->id.'-'.time().'.'.$ext;
                   $sPath = public_path().'/temp/'.$tempImage->name;
                   $dPath = public_path().'/uploads/category/'.$newImageName;
                   File::copy($sPath, $dPath);

                //    generate Image Thumbnail
                //    $dPath = public_path().'/uploads/category/thumb/'.$newImageName;
                //    $img = Image::make($sPath);
                //    $img->resize(450, 600); not use
                //    $img->save($dPath);

                   $category->image = $newImageName;
                   $category->save();

                //    Delete Old Images Here
                File::delete(public_path().'/uploads/category/thumb'.$oldImage);
                File::delete(public_path().'/uploads/category/'.$oldImage);



                }
               

               $request->flash('success', 'Category updated successfully');   

               return response()->json([
                'status' => true,
                'message' => 'Category updated successfully'
            ]);

            } else{
                return response()->json([
                    'status' => false,
                    'errors' => $validator->errors(),
                ]);
            }   
    }



    public function destroy($categoryId, Request $request){
        $category = Category::find($categoryId);

        if (empty($category)) {

        $request->flash('error', 'Category not found');   
            return response()->json([
                'status' => true,
                'mssage' => 'Category not found',
            ]);
    
            // return redirect()->route('categories.index');
        }
        
        File::delete(public_path().'/uploads/category/thumb'.$category->image);
        File::delete(public_path().'/uploads/category/'.$category->image);

        $category->delete();

        $request->flash('success', 'Category deleted successfully');   

        return response()->json([
            'status' => true,
            'mssage' => 'Category deleted successfully',
        ]);

    }
}
