<?php

namespace App\Http\Controllers\Api;

use App\Models\Incident;
use App\Events\NewSurveyAvailable;
use App\Events\IncidentUpdated;
use App\Events\AssetUpdated;
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
        
        // เก็บค่า category, priority, status จาก frontend ไว้ก่อน (ค่าที่แสดงผล)
        $displayCategory = $data['category'] ?? null;
        $displayPriority = $data['priority'] ?? null;
        $displayStatus = $data['status'] ?? null;
        
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
        
        // ใส่ค่า category, priority, status กลับเข้าไปเพื่อให้บันทึกลงฐานข้อมูล
        if ($displayCategory) $data['category'] = $displayCategory;
        if ($displayPriority) $data['priority'] = $displayPriority;
        if ($displayStatus) $data['status'] = $displayStatus;
        
        return $data;
    }

    public function store(Request $request)
    {
        // Map frontend field names to backend
        $mappedData = $this->mapRequestData($request);
        $request->merge($mappedData);
        
        $data = $request->validate($this->validationRules);
        
        $model = Incident::create($data);

        // Update Asset status to Maintenance when incident is created with an asset
        if ($model->asset_id) {
            $asset = \App\Models\Asset::find($model->asset_id);
            if ($asset) {
                // Save previous status before changing to Maintenance
                $model->previous_asset_status = $asset->status;
                $model->save();
                
                $asset->update(['status' => 'Maintenance']);
                
                // Broadcast asset updated event
                broadcast(new AssetUpdated($asset->fresh(), 'updated'))->toOthers();
            }
        }
        
        // Load the assignee relationship to return the technician name
        $model->load('assignee');
        
        // Add technician name to response for easier frontend consumption
        $response = $model->toArray();
        
        // Remove nested assignee object to avoid confusion
        if (isset($response['assignee'])) {
            unset($response['assignee']);
        }
        
        // Add flat field for technician name
        $response['assigned_to_name'] = $model->assignee ? $model->assignee->name : null;

        // Broadcast new incident created event
        broadcast(new IncidentUpdated($model, 'created'))->toOthers();

        return response()->json($response, 201);
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

            \Log::info('=== INCIDENT CLOSE DEBUG ===');
            \Log::info('Incident ID: ' . $model->id);
            \Log::info('Old Status: ' . $oldStatus);
            \Log::info('New Status: ' . $data['status']);
            \Log::info('Asset ID: ' . ($model->asset_id ?? 'NULL'));
            \Log::info('Previous Asset Status saved: ' . ($model->previous_asset_status ?? 'NULL'));

            // Update Asset status back to previous status (or Available) when incident is closed
            if ($model->asset_id) {
                $asset = \App\Models\Asset::find($model->asset_id);
                if ($asset) {
                    \Log::info('Asset found: ' . $asset->name . ', Current status: ' . $asset->status);
                    
                    // Restore previous status if saved, otherwise default to Available
                    $previousStatus = $model->previous_asset_status;
                    if ($previousStatus && $previousStatus !== 'Maintenance') {
                        \Log::info('Restoring to previous status: ' . $previousStatus);
                        $asset->update(['status' => $previousStatus]);
                    } else {
                        // Default to Available if no previous status was saved
                        \Log::info('No previous status, defaulting to Available');
                        $asset->update(['status' => 'Available']);
                    }
                    
                    \Log::info('Asset status after update: ' . $asset->fresh()->status);
                    
                    // Broadcast asset updated event
                    broadcast(new AssetUpdated($asset->fresh(), 'updated'))->toOthers();
                } else {
                    \Log::warning('Asset not found for ID: ' . $model->asset_id);
                }
            } else {
                \Log::info('No asset_id linked to this incident');
            }

            // Create MaintenanceHistory record when incident is closed with an asset
            if ($model->asset_id) {
                \App\Models\MaintenanceHistory::create([
                    'asset_id' => $model->asset_id,
                    'incident_id' => $model->id,
                    'title' => $model->title,
                    'description' => $data['repair_details'] ?? $model->repair_details ?: $model->description ?: 'ซ่อมแซมตาม incident',
                    'repair_status' => $data['repair_status'] ?? $model->repair_status ?? 'Completed',
                    'technician_id' => $model->assignee_id,
                    'technician_name' => $model->assignee ? $model->assignee->name : null,
                    'start_date' => $model->start_repair_date ?? $model->created_at,
                    'completion_date' => $data['completion_date'] ?? $model->completion_date ?? now(),
                    'has_cost' => $data['has_additional_cost'] ?? $model->has_additional_cost ?? false,
                    'cost' => $data['additional_cost'] ?? $model->additional_cost,
                    'replacement_equipment' => $data['replacement_equipment'] ?? $model->replacement_equipment,
                ]);
            }
        }

        $model->fill($data);
        $model->save();
        
        // Load the assignee relationship to return the technician name
        $model->load('assignee');
        
        // Add technician name to response for easier frontend consumption
        $response = $model->toArray();
        
        // Remove nested assignee object to avoid confusion
        if (isset($response['assignee'])) {
            unset($response['assignee']);
        }
        
        // Add flat field for technician name
        $response['assigned_to_name'] = $model->assignee ? $model->assignee->name : null;

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

        return response()->json($response);
    }

    public function show($id)
    {
        $model = Incident::with(['assignee', 'requester', 'satisfactionSurvey'])->findOrFail($id);
        
        $response = $model->toArray();
        
        // Remove nested assignee object to avoid confusion
        if (isset($response['assignee'])) {
            unset($response['assignee']);
        }
        
        // Add flat field for technician name
        $response['assigned_to_name'] = $model->assignee ? $model->assignee->name : null;

        return response()->json($response);
    }

    public function index(Request $request)
    {
        $query = Incident::with(['assignee', 'requester', 'satisfactionSurvey']);

        // รองรับ pagination เบื้องต้น ?per_page=20
        if ($request->has('per_page')) {
            $paginated = $query->paginate((int) $request->get('per_page', 15));
            // Add assigned_to_name to each item
            $paginated->getCollection()->transform(function ($incident) {
                $item = $incident->toArray();
                
                // Remove nested assignee object to avoid confusion
                if (isset($item['assignee'])) {
                    unset($item['assignee']);
                }
                
                // Add flat field for technician name
                $item['assigned_to_name'] = $incident->assignee ? $incident->assignee->name : null;
                return $item;
            });
            return $paginated;
        }

        $incidents = $query->get();
        // Add assigned_to_name to each item
        return $incidents->map(function ($incident) {
            $item = $incident->toArray();
            
            // Remove nested assignee object to avoid confusion
            if (isset($item['assignee'])) {
                unset($item['assignee']);
            }
            
            // Add flat field for technician name
            $item['assigned_to_name'] = $incident->assignee ? $incident->assignee->name : null;
            return $item;
        });
    }
}
