<?php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Models\Customer;
use App\Models\TierSetting;
use Illuminate\Http\Request;
use App\Models\OperationModel;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Pagination\Paginator;
use Illuminate\Pagination\LengthAwarePaginator;

class TierController extends Controller
{
    public function index()
    {
        return view('tiers.index');
    }

    public function setting(Request $request)
    {
        $tiers = new TierSetting();
        if(!empty($request->search)){
            $tiers = $tiers->where('label_name', 'like', '%'.$request->search.'%');
        }
        $tiers = $tiers->paginate(10);
        return view('tiers.setting', compact('tiers'));
    }

    public function store_setting(Request $request)
    {
        $request->validate([
            'label_name' => 'required',
            'min_range' => 'required',
        ]);

        try {
            $max_range = $request->max_range == 0 ? null : $request->max_range;
            $TierSetting = TierSetting::create([
                'label_name' => $request->label_name,
                'min_range' => $request->min_range,
                'max_range' => $max_range,
            ]);
            return response()->json([
                'status' => 'success',
                'message' => 'Tier setting has been created successfully',
            ]);
        } catch (\Throwable $th) {
            return response()->json([
                'status' => 'error',
                'message' => $th->getMessage(),
            ]);
        }
    }

    public function update_setting(TierSetting $tierSetting, Request $request)
    {
        $request->validate([
            'label_name' => 'required',
            'min_range' => 'required',
        ]);
        try {
            $max_range = $request->max_range == 0 ? null : $request->max_range;
            $tierSetting->update([
                'label_name' => $request->label_name,
                'min_range' => $request->min_range,
                'max_range' => $max_range,
            ]);
            return response()->json([
                'status' => 'success',
                'message' => 'Tier setting has been updated successfully',
            ]);
        } catch (\Throwable $th) {
            return response()->json([
                'status' => 'error',
                'message' => $th->getMessage(),
            ]);
        }
    }

    public function delete_setting(TierSetting $tierSetting)
    {
        try {
            $tierSetting->delete();
            return response()->json([
                'status' => 'success',
                'message' => 'Tier setting has been deleted successfully',
            ]);
        } catch (\Throwable $th) {
            return response()->json([
                'status' => 'error',
                'message' => $th->getMessage(),
            ]);
        }
    }

    public function customer_details(Request $request)
    {
        // dropdown data
        $tiers = TierSetting::all();
        $products = Product::all();
        $operational_models = OperationModel::all();
        $statuses = ['NEW' => 'NEW', 'REPEAT' => 'REPEAT'];
        return view('tiers.customer_details', compact('tiers', 'products', 'operational_models', 'statuses'));
    }

    public function customer_details_ajax(Request $request)
    {

        try {
            $results = DB::table('customers as c')
                ->leftJoin('order_details as od', 'od.customer_id', '=', 'c.id')
                ->leftJoin('products as p', 'p.id', '=', 'od.product_id')
                ->select(
                    'c.id',
                    'c.customer_name',
                    'c.customer_tel',
                    DB::raw('SUM(od.total_price) AS bv_all_price'),
                    DB::raw('SUM(od.total_unit) AS bv_all_unit'),
                    DB::raw('SUM(od.total_price) AS bv_product_price'),
                    DB::raw('SUM(od.total_unit) AS bv_product_unit'),
                    DB::raw("CASE WHEN MAX(od.buy_retain_status) = 1 THEN 'NEW' WHEN MAX(od.buy_retain_status) > 1 THEN 'REPEAT' ELSE 'NEW' END AS status")
                )
                ->where('p.product_foc',0);
                // ->whereIn('c.customer_tel', ['60194161329', '60148275839', '60177867129']);

            $results = $this->filtering_bv($results, $request);

            $results = $results->groupBy('c.id')
                ->orderBy(DB::raw('MAX(od.payment_at)'), 'DESC')
                ->get();

            return response()->json([
                'status' => 'success',
                'data' => $results,
            ]);

        } catch (\Throwable $th) {
            return response()->json([
                'status' => 'error',
                'message' => $th->getMessage(),
            ]);
        }
    }

    public function filtering_bv($data, $request)
    {
        $data = $data

        //start date
        ->when($request->filled('start_date'), function ($query) use ($request) {
            $start_date = Carbon::parse($request->start_date)->format('Y-m-d');
            $query->whereDate('od.payment_at', '>=', $start_date);
        })

        //end date
        ->when($request->filled('end_date'), function ($query) use ($request) {
            $end_date = Carbon::parse($request->end_date)->format('Y-m-d');
            $query->whereDate('od.payment_at', '<=', $end_date);
        })

        // input search
        ->when($request->filled('search'), function ($query) use ($request) {
            $query->where('c.customer_name', 'like', '%'.$request->search.'%')
                ->orWhere('c.customer_tel', 'like', '%'.$request->search.'%');
        })

        // filter by product
        ->when($request->filled('products'), function ($query) use ($request) {
            $query->select(
                'c.id',
                'c.customer_name',
                'c.customer_tel',
                DB::raw('SUM(od.total_price) AS bv_all_price'),
                DB::raw('SUM(od.total_unit) AS bv_all_unit'),
                DB::raw('SUM(CASE WHEN od.product_id IN ('.implode(',', $request->products).') THEN od.total_price ELSE 0 END) AS bv_product_price'),
                DB::raw('SUM(CASE WHEN od.product_id IN ('.implode(',', $request->products).') THEN od.total_unit ELSE 0 END) AS bv_product_unit'),
                DB::raw("CASE WHEN MAX(od.buy_retain_status) = 1 THEN 'NEW' WHEN MAX(od.buy_retain_status) > 1 THEN 'REPEAT' ELSE 'NEW' END AS status")
            );
        })

        //filter by operational model
        ->when($request->filled('op_ids'), function ($query) use ($request) {
            $query->whereIn('od.operation_model_id', $request->op_ids);
        })

        // filter by tier
        ->when($request->filled('tiers'), function ($query) use ($request) {
            $tiers = TierSetting::where('id', $request->tiers)->first();
            if($tiers->max_range != null){
                $query->having('bv_all_price', '>=', $tiers->min_range)
                    ->having('bv_all_price', '<=', $tiers->max_range);
            }else{
                $query->having('bv_all_price', '>=', $tiers->min_range);
            }
        })

        // filter by status
        ->when($request->filled('status'), function ($query) use ($request) {
            $query->having('status', $request->status);
        });

        return $data;
    }
}
