<?php

namespace App\Traits;

use App\Models\ActivityLog;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Auth;

trait LogsActivity
{
    protected static function bootLogsActivity()
    {
        static::created(function ($model) {
            $model->logActivity('created');
        });

        static::updated(function ($model) {
            $model->logActivity('updated');
        });

        static::deleted(function ($model) {
            $model->logActivity('deleted');
        });
    }

    public function logActivity($action)
    {
        $description = $this->getActivityDescription($action);
        
        ActivityLog::create([
            'user_id' => Auth::id(),
            'action' => $action,
            'model_type' => get_class($this),
            'model_id' => $this->id,
            'description' => $description,
            'properties' => [
                'attributes' => $this->sanitizeActivityAttributes($this->getAttributes()),
                'original' => $this->sanitizeActivityAttributes($this->getOriginal()),
            ],
            'ip_address' => request()->ip(),
            'user_agent' => request()->userAgent(),
        ]);
    }

    /**
     * Remove sensitive attributes before persisting activity snapshots.
     *
     * @param  array<string, mixed>  $attributes
     * @return array<string, mixed>
     */
    protected function sanitizeActivityAttributes(array $attributes): array
    {
        return Arr::except($attributes, $this->activityLogExcludedAttributes());
    }

    /**
     * Determine which attributes should never be stored in activity logs.
     *
     * @return array<int, string>
     */
    protected function activityLogExcludedAttributes(): array
    {
        $hidden = method_exists($this, 'getHidden') ? $this->getHidden() : [];

        return array_values(array_unique([
            ...$hidden,
            'password',
            'remember_token',
        ]));
    }

    protected function getActivityDescription($action)
    {
        $name = class_basename($this);
        return "{$name} was {$action}";
    }
}
