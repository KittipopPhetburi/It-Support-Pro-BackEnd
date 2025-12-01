<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

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

    public function index(Request $request)
    {
        $query = call_user_func([$this->modelClass, 'query']);

        // รองรับ pagination เบื้องต้น ?per_page=20
        if ($request->has('per_page')) {
            return $query->paginate((int) $request->get('per_page', 15));
        }

        return $query->get();
    }

    public function store(Request $request)
    {
        $data = $this->validationRules
            ? $request->validate($this->validationRules)
            : $request->all();

        $model = call_user_func([$this->modelClass, 'create'], $data);

        return response()->json($model, 201);
    }

    public function show($id)
    {
        $model = call_user_func([$this->modelClass, 'findOrFail'], $id);

        return response()->json($model);
    }

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

    public function destroy($id)
    {
        $model = call_user_func([$this->modelClass, 'findOrFail'], $id);
        $model->delete();

        return response()->json(null, 204);
    }
}
