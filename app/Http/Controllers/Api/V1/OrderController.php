<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Filters\V1\OrderFilter;
use App\Http\Requests\Api\V1\StoreOrderRequest;
use App\Http\Requests\Api\V1\UpdateOrderRequest;
use App\Http\Resources\V1\OrderCollection;
use App\Http\Resources\V1\OrderResource;
use App\Models\Order;
use App\Policies\V1\OrderPolicy;
use App\Services\V1\OrdersService;
use Illuminate\Database\QueryException;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;
use Throwable;

class OrderController extends ApiController
{
    protected $policyClass = OrderPolicy::class;

    public function __construct(
        protected OrdersService $orderService
    ) {}

    /**
     * Display a listing of the resource.
     */
    public function index(OrderFilter $filter)
    {
        Gate::authorize('view-any', Order::class);

        return new OrderCollection(
            Order::filter($filter)->paginate()
        );
    }

    /**
     * Display the specified resource.
     */
    public function show(Order $order)
    {
        Gate::authorize('view', $order);

        if ($this->include('user')) {
            $order->load('user');
        }

        $order->load('products');

        return new OrderResource($order);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreOrderRequest $request)
    {
        Gate::authorize('create', Order::class);

        try {
            $order = $this->orderService->createOrderHandleProducts($request);

            return response()->json(new OrderResource($order), Response::HTTP_CREATED);
        } catch (QueryException $eQueryException) {
            DB::rollback(); // Rollback transaction on database error

            return $this->responseDbError();
        } catch (Throwable $eTh) {
            DB::rollback(); // Rollback transaction on any other error

            return $this->responseUnexpectedError();
        }
    }

    /**
     * Update the specified resource in storage.
     * PATCH
     */
    public function update(UpdateOrderRequest $request, Order $order)
    {
        Gate::authorize('update', $order);

        try {
            $this->orderService->updateOrderHandleProducts($request, $order);

            return response()->json(new OrderResource($order), Response::HTTP_OK);
        } catch (QueryException $eQueryException) {
            DB::rollback(); // Rollback transaction on database error

            return $this->responseDbError();
        } catch (Throwable $eTh) {
            DB::rollback(); // Rollback transaction on any other error

            return $this->responseUnexpectedError();
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Order $order)
    {
        Gate::authorize('delete', $order);
        $this->orderService->deleteOrderHandleProducts($order);

        return $this->responseSuccess('Order deleted successfully');
    }
}
