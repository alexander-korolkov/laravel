<?php

namespace App\Http\Controllers;

use App\Models\Note;
use App\Models\Todo;
use Illuminate\Http\Request;

class NotesController extends Controller
{
    public function index()
    {
        return Note::with('todos')->get();
    }

    public function store(Request $request)
    {
        $note = new Note();
        $note->title = $request->input('title');
        $note->save();

        // Create todos for the note
        if ($request->has('todos')) {
            foreach ($request->input('todos') as $todo) {
                $newTodo = new Todo();
                $newTodo->note_id = $note->id;
                $newTodo->text = $todo['text'];
                $newTodo->completed = $todo['completed'];
                $newTodo->save();
            }
        }

        return response()->json($note, 201);
    }

    public function show($id)
    {
        return Note::with('todos')->findOrFail($id);
    }

    public function update(Request $request, $id)
    {
        $note = Note::findOrFail($id);
        $note->title = $request->input('title');
        $note->save();

        // Update or create todos for the note
        if ($request->has('todos')) {
            foreach ($request->input('todos') as $todo) {
                if (isset($todo['id'])) {
                    $existingTodo = Todo::findOrFail($todo['id']);
                    $existingTodo->text = $todo['text'];
                    $existingTodo->completed = $todo['completed'];
                    $existingTodo->save();
                } else {
                    $newTodo = new Todo();
                    $newTodo->note_id = $note->id;
                    $newTodo->text = $todo['text'];
                    $newTodo->completed = $todo['completed'];
                    $newTodo->save();
                }
            }
        }

        return response()->json($note, 200);
    }

    public function destroy($id)
    {
        $note = Note::findOrFail($id);
        $note->delete();

        // Delete associated todos
        Todo::where('note_id', $id)->delete();

        return response()->json(null, 204);
    }
}
