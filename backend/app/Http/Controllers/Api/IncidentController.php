<?php

namespace App\Http\Controllers\Api;

use App\Models\Incident;
use App\Events\NewSurveyAvailable;
use App\Events\IncidentUpdated;
use Illuminate\Http\Request;

class IncidentController extends BaseCrudController
{
    protected string $modelClass = Incident::class;

    protected array $validationRules = [
        'title' => 'required|string|max:255',
        'description' => 'nullable|string',
        'priority' => 'required|in:Low,Medium,High,Critical',
        'status' => 'required|in:Open,In Progress,Pending,Resolved,Closed',
        'category' => 'nullable|string|max:255',
        'subcategory' => 'nullable|string|max:255',

        'requester_id' => 'required|integer|exists:users,id',
        'reported_by_id' => 'nullable|integer|exists:users,id',
        'assignee_id' => 'nullable|integer|exists:users,id',

        'resolved_at' => 'nullable|date',
        'closed_at' => 'nullable|date',

        'branch_id' => 'nullable|integer|exists:branches,id',
        'department_id' => 'nullable|integer|exists:departments,id',
        'organization' => 'nullable|string|max:255',

        'contact_method' => 'nullable|string|max:255',
        'contact_phone' => 'nullable|string|max:50',
        'location' => 'nullable|string|max:255',

        'asset_id' => 'nullable|integer|exists:assets,id',
        'asset_name' => 'nullable|string|max:255',
        'asset_brand' => 'nullable|string|max:255',
        'asset_model' => 'nullable|string|max:255',
        'asset_serial_number' => 'nullable|string|max:255',
        'asset_inventory_number' => 'nullable|string|max:255',
        'is_custom_asset' => 'nullable|boolean',
        'equipment_type' => 'nullable|string|max:255',
        'operating_system' => 'nullable|string|max:255',

        'start_repair_date' => 'nullable|date',
        'completion_date' => 'nullable|date',
        'repair_details' => 'nullable|string',
        'repair_status' => 'nullable|string|max:255',
        'replacement_equipment' => 'nullable|string|max:255',
        'has_additional_cost' => 'nullable|boolean',
        'additional_cost' => 'nullable|numeric',

        'technician_signature' => 'nullable|string',
        'customer_signature' => 'nullable|string',

        'satisfaction_rating' => 'nullable|integer|min:1|max:5',
        'satisfaction_comment' => 'nullable|string',
        'satisfaction_date' => 'nullable|date',
    ];

    // Update rules - requester_id ไม่ required เพราะไม่ควรเปลี่ยนผู้แจ้งตอน update
    protected array $updateValidationRules = [
        'title' => 'required|string|max:255',
        'description' => 'nullable|string',
        'priority' => 'required|in:Low,Medium,High,Critical',
        'status' => 'required|in:Open,In Progress,Pending,Resolved,Closed',
        'category' => 'nullable|string|max:255',
        'subcategory' => 'nullable|string|max:255',

        'requester_id' => 'nullable|integer|exists:users,id',
        'reported_by_id' => 'nullable|integer|exists:users,id',
        'assignee_id' => 'nullable|integer|exists:users,id',

        'resolved_at' => 'nullable|date',
        'closed_at' => 'nullable|date',

        'branch_id' => 'nullable|integer|exists:branches,id',
        'department_id' => 'nullable|integer|exists:departments,id',
        'organization' => 'nullable|string|max:255',

        'contact_method' => 'nullable|string|max:255',
        'contact_phone' => 'nullable|string|max:50',
        'location' => 'nullable|string|max:255',

        'asset_id' => 'nullable|integer|exists:assets,id',
        'asset_name' => 'nullable|string|max:255',
        'asset_brand' => 'nullable|string|max:255',
        'asset_model' => 'nullable|string|max:255',
        'asset_serial_number' => 'nullable|string|max:255',
        'asset_inventory_number' => 'nullable|string|max:255',
        'is_custom_asset' => 'nullable|boolean',
        'equipment_type' => 'nullable|string|max:255',
        'operating_system' => 'nullable|string|max:255',

        'start_repair_date' => 'nullable|date',
        'completion_date' => 'nullable|date',
        'repair_details' => 'nullable|string',
        'repair_status' => 'nullable|string|max:255',
        'replacement_equipment' => 'nullable|string|max:255',
        'has_additional_cost' => 'nullable|boolean',
        'additional_cost' => 'nullable|numeric',

        'technician_signature' => 'nullable|string',
        'customer_signature' => 'nullable|string',

        'satisfaction_rating' => 'nullable|integer|min:1|max:5',
        'satisfaction_comment' => 'nullable|string',
        'satisfaction_date' => 'nullable|date',
    ];

    /**
     * Map frontend field names to backend field names
     */
    protected function mapRequestData(Request $request): array
    {
        $data = $request->all();
        
        // Map assigned_to to assignee_id (frontend sends assigned_to, backend expects assignee_id)
        if (isset($data['assigned_to']) && !isset($data['assignee_id'])) {
            $data['assignee_id'] = $data['assigned_to'];
        }
        
        // Remove fields that are not in the database
        unset($data['assigned_to']);
        unset($data['assigned_to_name']);
        unset($data['incident_category_id']);
        unset($data['priority_id']);
        unset($data['status_id']);
        unset($data['requester']);
        unset($data['reported_by']);
        unset($data['branch']);
        unset($data['department']);
        
        return $data;
    }

    public function store(Request $request)
    {
        // Map frontend field names to backend
        $mappedData = $this->mapRequestData($request);
        $request->merge($mappedData);
        
        $data = $request->validate($this->validationRules);
        
        $model = Incident::create($data);
        
        // Load the assignee relationship to return the technician name
        $model->load('assignee');

        // Broadcast new incident created event
        broadcast(new IncidentUpdated($model, 'created'))->toOthers();

        return response()->json($model, 201);
    }

    public function update(Request $request, $id)
    {
        $model = Incident::findOrFail($id);
        $oldStatus = $model->status;
        
        // Map frontend field names to backend
        $mappedData = $this->mapRequestData($request);
        $request->merge($mappedData);
        
        $rules = $this->updateValidationRules ?: $this->validationRules;
        $data = $request->validate($rules);

        // Auto-set resolved_at when status changes to Resolved
        if (isset($data['status']) && $data['status'] === 'Resolved' && $oldStatus !== 'Resolved') {
            $data['resolved_at'] = now();
        }
        
        // Auto-set closed_at when status changes to Closed
        if (isset($data['status']) && $data['status'] === 'Closed' && $oldStatus !== 'Closed') {
            $data['closed_at'] = now();
        }

        $model->fill($data);
        $model->save();
        
        // Load the assignee relationship to return the technician name
        $model->load('assignee');

        // Broadcast events for real-time updates
        $newStatus = $data['status'] ?? $model->status;
        
        // Broadcast incident update to all listeners
        broadcast(new IncidentUpdated($model, 'updated'))->toOthers();
        
        // When incident is resolved, notify the requester to complete satisfaction survey
        if ($newStatus === 'Resolved' && $oldStatus !== 'Resolved') {
            // Create survey data
            $surveyData = [
                'incident_id' => $model->id,
                'ticket_id' => $model->ticket_id ?? 'INC-' . str_pad($model->id, 6, '0', STR_PAD_LEFT),
                'title' => $model->title,
                'technician_name' => $model->assignee ? $model->assignee->name : 'ไม่ระบุ',
            ];
            
            // Broadcast to the requester
            if ($model->requester_id) {
                broadcast(new NewSurveyAvailable($model->requester_id, $surveyData));
            }
        }

        return response()->json($model);
    }

    public function show($id)
    {
        $model = Incident::with(['assignee', 'requester', 'satisfactionSurvey'])->findOrFail($id);

        return response()->json($model);
    }

    public function index(Request $request)
    {
        $query = Incident::with(['assignee', 'requester', 'satisfactionSurvey']);

        // รองรับ pagination เบื้องต้น ?per_page=20
        if ($request->has('per_page')) {
            return $query->paginate((int) $request->get('per_page', 15));
        }

        return $query->get();
    }
}
