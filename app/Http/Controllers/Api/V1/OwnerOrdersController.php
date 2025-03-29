<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Filters\V1\OrderFilter;
use App\Http\Requests\Api\V1\StoreOrderRequest;
use App\Http\Resources\V1\OrderCollection;
use App\Http\Resources\V1\OrderResource;
use App\Models\Order;
use App\Policies\V1\OwnerPolicy;
use App\Services\V1\OrdersService;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Database\QueryException;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\DB;
use Throwable;

class OwnerOrdersController extends ApiController
{
    use AuthorizesRequests;

    protected $policyClass = OwnerPolicy::class;

    public function __construct(
        protected OrdersService $orderService
    ) {}

    /**
     * Display a listing of the resource.
     */
    public function index($ownerId, OrderFilter $filter)
    {
        try {
            $this->authIsOwner($ownerId);

            return response()->json(new OrderCollection(
                Order::where('user_id', $ownerId)->filter($filter)->paginate()
            ), Response::HTTP_OK);
        } catch (ModelNotFoundException $eModelNotFound) {
            return $this->notFound('User not found');
        } catch (AuthorizationException $eAuthorizationException) {
            return $this->notAuthorized();
        }
    }

    /**
     * Display the specified resource.
     */
    public function show($ownerId, $orderId)
    {
        try {
            $order = Order::where('id', $orderId)->where('user_id', $ownerId)->firstOrFail();
            $this->isAbleIsOwner('view', $order, $ownerId); // policy

            if ($this->include('user')) {
                $order->load('user');
            }

            $order->load('products');

            return response()->json(new OrderResource($order), Response::HTTP_OK);
        } catch (ModelNotFoundException $eModelNotFound) {
            return $this->notFound('User/Order not found');
        } catch (AuthorizationException $eAuthorizationException) {
            return $this->notAuthorized();
        }
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store($ownerId, StoreOrderRequest $request)
    {
        try {
            $this->authIsOwner($ownerId);
            $order = $this->orderService->createOrderHandleProducts($request);

            return response()->json(new OrderResource($order), Response::HTTP_CREATED);
        } catch (ModelNotFoundException $eModelNotFound) {
            return $this->notFound('User/Order not found');
        } catch (AuthorizationException $eAuthorizationException) {
            return $this->notAuthorized();
        } catch (QueryException $eQueryException) {
            DB::rollback(); // Rollback transaction on database error

            return $this->dbError();
        } catch (Throwable $eTh) {
            DB::rollback(); // Rollback transaction on any other error

            return $this->unexpectedError();
        }
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy($ownerId, $orderId)
    {
        try {
            $order = Order::where('id', $orderId)->where('user_id', $ownerId)->firstOrFail();
            $this->isAbleIsOwner('delete', $order, $ownerId); // policy
            $order->delete();

            return $this->success('Order deleted successfully');
        } catch (ModelNotFoundException $eModelNotFound) {
            return $this->notFound('User/Order not found');
        } catch (AuthorizationException $eAuthorizationException) {
            return $this->notAuthorized();
        }
    }
}
