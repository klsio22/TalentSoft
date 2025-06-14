<?php

require_once __DIR__ . '/../../config/bootstrap.php';

use App\Models\Employee;
use App\Models\Project;
use App\Models\Notification;
use Core\Database\Database;

echo "Creating sample projects...\n";

// Project data
// Define constants for project statuses
const STATUS_IN_PROGRESS = 'In Progress';
const STATUS_COMPLETED = 'Completed';
const STATUS_CANCELED = 'Canceled';

$projects = [
    [
        'name' => 'TalentSoft HR System',
        'description' => 'Development of the internal HR management system with employee profiles, project assignments, and performance tracking.',
        'start_date' => '2023-01-15',
        'end_date' => '2023-12-31',
        'status' => STATUS_COMPLETED,
        'budget' => 150000.00
    ],
    [
        'name' => 'Mobile App Development',
        'description' => 'Creating a mobile application for employees to access company resources, request time off, and view their schedules.',
        'start_date' => '2023-06-01',
        'end_date' => '2024-03-31',
        'status' => STATUS_IN_PROGRESS,
        'budget' => 85000.00
    ],
    [
        'name' => 'Cloud Migration',
        'description' => 'Migrating all company systems and data to cloud infrastructure to improve scalability and reduce costs.',
        'start_date' => '2023-09-15',
        'end_date' => '2024-06-30',
        'status' => STATUS_IN_PROGRESS,
        'budget' => 120000.00
    ],
    [
        'name' => 'Website Redesign',
        'description' => 'Complete overhaul of the company website with modern design, improved UX, and integration with internal systems.',
        'start_date' => '2023-03-01',
        'end_date' => '2023-08-31',
        'status' => STATUS_COMPLETED,
        'budget' => 45000.00
    ],
    [
        'name' => 'Data Analytics Platform',
        'description' => 'Building a comprehensive data analytics platform to provide insights into company performance and employee productivity.',
        'start_date' => '2024-01-10',
        'end_date' => '2024-12-15',
        'status' => STATUS_IN_PROGRESS,
        'budget' => 200000.00
    ],
    [
        'name' => 'Security Enhancement',
        'description' => 'Implementing advanced security measures across all company systems to protect against cyber threats.',
        'start_date' => '2024-02-15',
        'end_date' => '2024-08-31',
        'status' => STATUS_IN_PROGRESS,
        'budget' => 75000.00
    ],
    [
        'name' => 'Customer Portal',
        'description' => 'Developing a portal for customers to access their account information, submit support tickets, and track orders.',
        'start_date' => '2023-11-01',
        'end_date' => '2024-05-31',
        'status' => STATUS_IN_PROGRESS,
        'budget' => 95000.00
    ]
];

// Create projects
$createdProjects = [];
foreach ($projects as $projectData) {
    $project = new Project($projectData);
    if ($project->save()) {
        $createdProjects[] = $project;
        echo "Project created: {$project->name}\n";
    } else {
        echo "Failed to create project: {$projectData['name']}\n";
    }
}

// Get all employees
$employees = Employee::all();

// Project roles
$projectRoles = ['Developer', 'Designer', 'QA Engineer', 'Project Manager', 'Business Analyst', 'DevOps Engineer'];

// Assign employees to projects
echo "\nAssigning employees to projects...\n";
foreach ($createdProjects as $project) {
    // Assign 3-8 random employees to each project
    $numEmployees = rand(3, 8);
    $assignedEmployees = [];
    
    for ($i = 0; $i < $numEmployees; $i++) {
        // Get a random employee that hasn't been assigned to this project yet
        do {
            $randomEmployee = $employees[array_rand($employees)];
        } while (in_array($randomEmployee->id, $assignedEmployees));
        
        $assignedEmployees[] = $randomEmployee->id;
        $role = $projectRoles[array_rand($projectRoles)];
        
        // Create relationship in pivot table
        $pdo = Database::getDatabaseConn();
        $sql = "INSERT INTO Employee_Projects (employee_id, project_id, role) VALUES (:employee_id, :project_id, :role)";
        $stmt = $pdo->prepare($sql);
        $stmt->bindValue(':employee_id', $randomEmployee->id);
        $stmt->bindValue(':project_id', $project->id);
        $stmt->bindValue(':role', $role);
        
        if ($stmt->execute()) {
            echo "Assigned {$randomEmployee->name} to project '{$project->name}' as {$role}\n";
            
            // Create a notification for the employee about the project assignment
            $notification = new Notification([
                'employee_id' => $randomEmployee->id,
                'type' => 'Project',
                'message' => "You have been assigned to the project '{$project->name}' as {$role}.",
                'status' => 'Unread'
            ]);
            $notification->save();
        }
    }
}

// Create additional notifications
echo "\nCreating additional notifications...\n";
$notificationTypes = ['Registration', 'Project', 'Approval'];
$notificationMessages = [
    'Registration' => [
        'Welcome to TalentSoft! Your account has been successfully created.',
        'Please complete your profile information as soon as possible.',
        'Your registration has been confirmed. You can now access all system features.'
    ],
    'Project' => [
        'A new milestone has been reached in your project.',
        'Project deadline has been extended by two weeks.',
        'You have been invited to a project review meeting tomorrow.',
        'Your project report is due by the end of this week.'
    ],
    'Approval' => [
        'Your time off request has been approved.',
        'Your project proposal has been reviewed and approved.',
        'Your expense report has been approved.',
        'Your training request has been approved by management.'
    ]
];

// Create 2-4 random notifications for each employee
foreach ($employees as $employee) {
    $numNotifications = rand(2, 4);
    
    for ($i = 0; $i < $numNotifications; $i++) {
        $type = $notificationTypes[array_rand($notificationTypes)];
        $messages = $notificationMessages[$type];
        $message = $messages[array_rand($messages)];
        
        $notification = new Notification([
            'employee_id' => $employee->id,
            'type' => $type,
            'message' => $message,
            'status' => rand(0, 1) ? 'Read' : 'Unread'
        ]);
        
        if ($notification->save()) {
            echo "Created notification for {$employee->name}: {$message}\n";
        }
    }
}

echo "\n=== SUMMARY ===\n";
echo "Total projects created: " . count($createdProjects) . "\n";
echo "Projects have been assigned to employees\n";
echo "Notifications have been created for all employees\n";
echo "\nData inserted successfully!\n";
