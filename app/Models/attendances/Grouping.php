<?php

namespace App\Models\attendances;

use App\Models\Reg\Campus;
use App\Models\Student;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Grouping extends Model
{
    protected $table = 'att_grouping';

    protected $fillable = [
        'name',
        'parent_id',
        'room_id', // Make sure this exists if groupings have direct room association
        'note',
        'created_by',
        'updated_by',
    ];

    // Direct room relationship (if grouping has room_id)
    // Relationship to get the room through the first student's pivot
    public function room()
    {
        return $this->hasOneThrough(
            Room::class,
            GroupingStudent::class,
            'grouping_id', // Foreign key on pivot table
            'id',          // Foreign key on rooms table
            'id',          // Local key on groupings table
            'room_id'      // Local key on pivot table
        );
    }

    // Accessor to combine names
    public function getGroupingWithRoomAttribute()
    {
        $roomName = $this->room?->name ?? 'No Room';

        return "{$this->name} - {$roomName}";
    }

    // If you need campus through room
    public function campus()
    {
        return $this->hasOneThrough(
            Campus::class,
            Room::class,
            'id',       // Foreign key on rooms table
            'id',       // Foreign key on campuses table
            'room_id',  // Local key on groupings table
            'campus_id' // Local key on rooms table
        );
    }

    // Students relationship with room_id in pivot
    public function students(): BelongsToMany
    {
        return $this->belongsToMany(Student::class, 'att_grouping_student', 'grouping_id', 'university_no')
            ->withPivot(['batch_code', 'room_id'])
            ->withTimestamps();
    }

    // If you need to access rooms through the pivot table
    public function rooms(): BelongsToMany
    {
        return $this->belongsToMany(Room::class, 'att_grouping_student', 'grouping_id', 'room_id')
            ->withTimestamps()
            ->distinct();
    }

    // Other relationships...
    public function lecture(): BelongsTo
    {
        return $this->belongsTo(Lecture::class, 'id', 'grouping_id');
    }

    public function parent()
    {
        return $this->belongsTo(Grouping::class, 'parent_id');
    }

    public function children()
    {
        return $this->hasMany(Grouping::class, 'parent_id');
    }
}
