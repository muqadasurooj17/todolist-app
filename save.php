<?php
header("Access-Control-Allow-Origin: http://localhost:3000");
header("Access-Control-Allow-Headers: Content-Type");
header("Access-Control-Allow-Methods: POST, GET, DELETE, OPTIONS");
header("Content-Type: application/json");

$dataFile = "todos.json";

// Handle preflight
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit();
}

// GET: Return all todos
if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    if (file_exists($dataFile)) {
        echo file_get_contents($dataFile);
    } else {
        echo json_encode(["status" => "error", "message" => "No todos found."]);
    }
    exit();
}

// POST: Save todos (replace all)
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $input = file_get_contents("php://input");
    $data = json_decode($input, true);

    if ($data) {
        file_put_contents($dataFile, json_encode($data, JSON_PRETTY_PRINT));
        echo json_encode(["status" => "success", "message" => "Todo saved successfully!", "received" => $data]);
    } else {
        echo json_encode(["status" => "error", "message" => "Invalid JSON data"]);
    }
    exit();
}

// DELETE: Delete a specific todo by id
if ($_SERVER['REQUEST_METHOD'] === 'DELETE') {
    $input = file_get_contents("php://input");
    $decoded = json_decode($input, true);
    $deleteId = $decoded["id"] ?? null;

    if (!$deleteId) {
        echo json_encode(["status" => "error", "message" => "Missing todo ID"]);
        exit();
    }

    if (!file_exists($dataFile)) {
        echo json_encode(["status" => "error", "message" => "No data file found"]);
        exit();
    }

    $todos = json_decode(file_get_contents($dataFile), true);

    // Filter out the todo by ID
    $todos = array_filter($todos, function ($todo) use ($deleteId) {
        return $todo["id"] !== $deleteId;
    });

    // Re-index and save updated todos
    file_put_contents($dataFile, json_encode(array_values($todos), JSON_PRETTY_PRINT));

    echo json_encode(["status" => "success", "message" => "Todo deleted successfully", "deleted_id" => $deleteId]);
    exit();
}

// Fallback
echo json_encode(["status" => "error", "message" => "Unsupported request method"]);
