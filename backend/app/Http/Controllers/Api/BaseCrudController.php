<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

/**
 * BaseCrudController - Controller ต้นแบบสำหรับ CRUD operations
 * 
 * ให้ Controller ย่อยสืบทอด (extends) แล้วกำหนด:
 * - $modelClass: ชื่อ Model class (เช่น Branch::class)
 * - $validationRules: กฎ validation สำหรับ store
 * - $updateValidationRules: กฎ validation สำหรับ update (optional ถ้าไม่กำหนดจะใช้ $validationRules)
 * 
 * Controller ย่อยสามารถ override แต่ละ method ได้ตามต้องการ
 * 
 * Routes ที่ใช้: apiResource (index, store, show, update, destroy)
 */
abstract class BaseCrudController extends Controller
{
    /**
     * ชื่อคลาสของโมเดล เช่น App\Models\Branch
     * ให้กำหนดใน controller ย่อย
     */
    protected string $modelClass;

    /**
     * กฎ validate ตอนสร้าง (store)
     */
    protected array $validationRules = [];

    /**
     * กฎ validate ตอนแก้ไข (update)
     * ถ้าไม่กำหนด จะใช้ $validationRules แทน
     */
    protected array $updateValidationRules = [];

    /**
     * ดึงข้อมูลทั้งหมด (GET /api/{resource})
     * รองรับ pagination: ?per_page=20
     * ถ้าไม่ส่ง per_page จะดึงทั้งหมด
     */
    public function index(Request $request)
    {
        $query = call_user_func([$this->modelClass, 'query']);

        // รองรับ pagination เบื้องต้น ?per_page=20
        if ($request->has('per_page')) {
            return $query->paginate((int) $request->get('per_page', 15));
        }

        return $query->get();
    }

    /**
     * สร้างข้อมูลใหม่ (POST /api/{resource})
     * - validate ตาม $validationRules (ถ้ามี)
     * - สร้าง model ด้วย $request->all() (fillable จะกรองเฉพาะ field ที่อนุญาต)
     * 
     * @return JsonResponse 201 Created
     */
    public function store(Request $request)
    {
        // Get all input data first
        $allData = $request->all();
        
        // Validate only if rules exist, but keep all data
        if ($this->validationRules) {
            $request->validate($this->validationRules);
        }

        // Use all data for create (fillable will filter what's allowed)
        $model = call_user_func([$this->modelClass, 'create'], $allData);

        return response()->json($model, 201);
    }

    /**
     * ดึงข้อมูลตาม ID (GET /api/{resource}/{id})
     * 
     * @param int $id
     * @return JsonResponse ข้อมูล model หรือ 404
     */
    public function show($id)
    {
        $model = call_user_func([$this->modelClass, 'findOrFail'], $id);

        return response()->json($model);
    }

    /**
     * แก้ไขข้อมูล (PUT /api/{resource}/{id})
     * - ใช้ $updateValidationRules ถ้ามี ไม่งั้นใช้ $validationRules
     * - fill + save เพื่ออัปเดตเฉพาะ field ที่ส่งมา
     * 
     * @param int $id
     * @return JsonResponse ข้อมูล model ที่อัปเดตแล้ว
     */
    public function update(Request $request, $id)
    {
        $model = call_user_func([$this->modelClass, 'findOrFail'], $id);

        $rules = $this->updateValidationRules ?: $this->validationRules;

        $data = $rules
            ? $request->validate($rules)
            : $request->all();

        $model->fill($data);
        $model->save();

        return response()->json($model);
    }

    /**
     * ลบข้อมูล (DELETE /api/{resource}/{id})
     * 
     * @param int $id
     * @return JsonResponse 204 No Content
     */
    public function destroy($id)
    {
        $model = call_user_func([$this->modelClass, 'findOrFail'], $id);
        $model->delete();

        return response()->json(null, 204);
    }
}
