<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\DB;

class MegaMenuController extends Controller
{
    /**
     * Get the mega menu structure with all categories, subcategories, materials, and series.
     * Response format:
     * [
     *   {
     *     "id": "PC",
     *     "name": "Pressure Cooker",
     *     "sort_order": 10,
     *     "sub_categories": [
     *       {
     *         "id": 1,
     *         "name": "Outerlid Pressure Cooker",
     *         "series": [
     *           {
     *             "id": "M1",
     *             "name": "Aluminium",
     *             "options": [
     *               {
     *                 "id": 1,
     *                 "name": "Prime",
     *                 "series_id": "M1",
     *                 "sizes": ["1.5L", "2L", "3L"]
     *               }
     *             ]
     *           }
     *         ]
     *       }
     *     ]
     *   }
     * ]
     */
    public function index()
    {
        // Fetch all categories ordered by sort_order
        $categories = DB::table('sm_product_categories')
            ->orderBy('sort_order', 'asc')
            ->get();

        $result = [];

        foreach ($categories as $cat) {
            $categoryData = [
                'id'               => $cat->category_id,
                'name'             => $cat->category_name,
                'sort_order'       => $cat->sort_order,
                'sub_categories'   => []
            ];

            // Get all unique subcategories for this category
            $subCategories = DB::table('sm_subcategories')
                ->join('sm_products_main', 'sm_products_main.product_id', '=', 'sm_subcategories.product_id')
                ->where('sm_products_main.category_id', $cat->category_id)
                ->select('sm_subcategories.subcat_id', 'sm_subcategories.subcat_name')
                ->distinct()
                ->get();

            foreach ($subCategories as $subCat) {
                $subCategoryData = [
                    'id'       => $subCat->subcat_id,
                    'name'     => $subCat->subcat_name,
                    'series'   => []
                ];

                // Get all unique materials (series) for this subcategory
                $materials = DB::table('sm_materials')
                    ->join('sm_product_details', 'sm_product_details.material_id', '=', 'sm_materials.material_id')
                    ->join('sm_subcategories', 'sm_subcategories.subcat_id', '=', 'sm_product_details.subcat_id')
                    ->join('sm_products_main', 'sm_products_main.product_id', '=', 'sm_product_details.product_id')
                    ->where('sm_subcategories.subcat_id', $subCat->subcat_id)
                    ->where('sm_products_main.category_id', $cat->category_id)
                    ->select('sm_materials.material_id', 'sm_materials.material_name')
                    ->distinct()
                    ->get();

                foreach ($materials as $material) {
                    $materialData = [
                        'id'       => $material->material_id,
                        'name'     => $material->material_name,
                        'options'  => []
                    ];

                    // Get all unique series (options) for this material & subcategory
                    $seriesOptions = DB::table('sm_series')
                        ->join('sm_product_details', 'sm_product_details.series_id', '=', 'sm_series.series_id')
                        ->join('sm_products_main', 'sm_products_main.product_id', '=', 'sm_product_details.product_id')
                        ->where('sm_product_details.material_id', $material->material_id)
                        ->where('sm_product_details.subcat_id', $subCat->subcat_id)
                        ->where('sm_products_main.category_id', $cat->category_id)
                        ->select('sm_series.series_id', 'sm_series.series_name')
                        ->distinct()
                        ->get();

                    foreach ($seriesOptions as $seriesOpt) {
                        $optionData = [
                            'id'        => $seriesOpt->series_id,
                            'name'      => $seriesOpt->series_name,
                            'series_id' => $material->material_id,
                            'sizes'     => []
                        ];

                        // Get all unique sizes (net_quantity) for this series
                        $sizes = DB::table('sm_product_details')
                            ->where('series_id', $seriesOpt->series_id)
                            ->where('material_id', $material->material_id)
                            ->where('subcat_id', $subCat->subcat_id)
                            ->where(function ($q) use ($cat) {
                                $q->whereIn('product_id', function ($query) use ($cat) {
                                    $query->select('product_id')
                                        ->from('sm_products_main')
                                        ->where('category_id', $cat->category_id);
                                });
                            })
                            ->select('net_quantity')
                            ->where('net_quantity', '!=', '')
                            ->where('net_quantity', '!=', null)
                            ->distinct()
                            ->orderBy('net_quantity', 'asc')
                            ->pluck('net_quantity')
                            ->toArray();

                        $optionData['sizes'] = $sizes;
                        $materialData['options'][] = $optionData;
                    }

                    if (count($materialData['options']) > 0) {
                        $subCategoryData['series'][] = $materialData;
                    }
                }

                if (count($subCategoryData['series']) > 0) {
                    $categoryData['sub_categories'][] = $subCategoryData;
                }
            }

            if (count($categoryData['sub_categories']) > 0) {
                $result[] = $categoryData;
            }
        }

        return response()->json($result);
    }
}
