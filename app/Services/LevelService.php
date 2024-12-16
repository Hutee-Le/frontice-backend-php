<?php

namespace App\Services;

use App\Models\Level;

class LevelService
{
    // Create a new level
    public function createLevel(array $data)
    {
        return Level::create([
            'name' => $data['name'],
            'default_point' => $data['default_point'],
            'required_point' => $data['required_point'],
        ]);
    }

    // Retrieve all levels
    public function getAllLevels()
    {
        return Level::all();
    }

    // Retrieve a single level by ID
    public function getLevelById($id)
    {
        return Level::findOrFail($id);
    }

    // Update a level
    public function updateLevel($id, array $data)
    {
        $level = Level::findOrFail($id);
        $level->update([
            'name' => $data['name'],
            'default_point' => $data['default_point'],
            'required_point' => $data['required_point'],
        ]);
        return $level;
    }

    // Delete a level
    public function deleteLevel($id)
    {
        $level = Level::findOrFail($id);
        return $level->delete();
    }
}
