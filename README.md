# Sleep App

## Overview

Sleep App is a resource management tool designed to optimize Kubernetes usage and reduce costs. It provides both manual and automated controls for Kubernetes workloads, allowing DevOps teams and developers to put resources to sleep when not needed.

With Laravel Backpack integration, Sleep App offers an intuitive web interface for managing Kubernetes Deployments and StatefulSets, eliminating the need for complex CLI commands or Kubernetes RBAC configurations.

## Features

-   **Project Management:** Manage projects and subservices efficiently.
-   **Manual & Auto Sleep:** Automatically sleep services when idle to save resources.
-   **Resource Control:** Soft and hard refresh options for Kubernetes resources.
-   **Application Structure:** Display applications and their states in a folder view.
-   **Time-Based Activation:** Activate a project or subservice for a set period.
-   **Dashboard View:** Click on rows to view project details in a structured manner.
-   **Exclude:** Don't show application in the Sleep App

## Data Flow

```
Project
└── Subservice
   └── Resources (Deployment & StatefulSet)
```

Example:

```
chatbot
└── english
   └── rasa-worker-en
   └── rasa-webserver-en
   └── rasa-mysql-en
└── indonesia
   └── rasa-worker-id
   └── rasa-webserver-id
   └── rasa-mysql-id
ai
└── milvus
   └── milvus-postgresql
   └── milvus-webserver
   mindsdb
   └── mindsdb-postgresql
   └── mindsdb-webserver
sleep-app
└── sleep-app-no-subservice
   └── sleep-app
```

## User Interface

### 1. Project Management

-   **Overview of all projects**
-   **Global Actions:**
    -   Refresh all Kubernetes resources
-   **Individual Actions:**
    -   View project details via popup
    -   Toggle Auto Sleep on/off
    -   Manage subservices
    -   Exclude or delete projects
    -   Manually sleep/activate subservices
    -   Hard refresh subservices

### 2. Subservice Management

-   **Overview of subservices**
-   **Active Time Remaining:** Displays time before auto-sleep.
-   **Global Actions:**
    -   Manually sleep/activate all subservices
    -   Exclude or hard refresh all subservices
-   **Individual Actions:**
    -   View subservice details via popup
    -   Manage resources
    -   Exclude or delete subservices
    -   Manually sleep/activate resources
    -   Hard refresh resources

### 3. Resource Management

-   **Overview of deployments/stateful sets**
-   **Active Time Remaining:** Shows time left before auto-sleep.
-   **Global Actions:**
    -   Manually sleep/activate all resources
    -   Exclude or hard refresh all resources
-   **Individual Actions:**
    -   View resource details via popup
    -   Edit replica count

### 4. All Subservices

-   Similar to Project Management but lists all subservices instead of projects.

### 5. Exclude Management

-   Define projects/subservices to exclude from Sleep App.

### 6. Application Lists

-   View all projects, subservices, and resources in a structured format.
-   Click to navigate deeper into subservices or resources.

## Adding a Service to Sleep App

To add a service, modify your `Deployment/StatefulSet` configuration and click "Refresh All Resources":

```
apiVersion: apps/v1
kind: Deployment
metadata:
  labels:
    app.kubernetes.io/managed-by: Helm
    application: apz
    component: exporter
    project: infra          # Add Project
    subservice: exporter    # Add Subservice (Optional)
  name: apz-exporter
  namespace: kube-logging
spec:
  template:
    metadata:
      labels:
        app: apz-exporter
        application: apz
        component: exporter
        project: infra        # Add Project
        subservice: exporter  # Add Subservice (Optional)
```

## Use Case & Main Functionalities

### 1. Activation

-   Activate project/subservice from sleep mode.
-   Extend active time manually.
-   Choose whether activation duration (1 hour from now) or activation time (until 13.00)
-   Default state is no auto sleep
    ![Activate](https://alfa-cdn-us.s3.ap-southeast-1.amazonaws.com/sleep-app-thesis/activate.gif)

### 2. Manual Sleep

-   Put services to sleep manually if not in use.
    ![Sleep](https://alfa-cdn-us.s3.ap-southeast-1.amazonaws.com/sleep-app-thesis/sleep.gif)

### 3. Auto Sleep & Activation Toggle

-   Services automatically enter sleep mode when active time reaches zero.
-   User must enable Auto Sleep
-   Default state is no auto sleep
    ![Auto Sleep](https://alfa-cdn-us.s3.ap-southeast-1.amazonaws.com/sleep-app-thesis/autosleep.gif)

### 4. Exclude

-   Add projects/subservices to the exclusion list and remove it from Sleep App
    ![Exclude](https://alfa-cdn-us.s3.ap-southeast-1.amazonaws.com/sleep-app-thesis/exclude.gif)

### 5. Hard Refresh

-   Clears resources from the database and syncs fresh data from Kubernetes.
    ![Hard Refresh](https://alfa-cdn-us.s3.ap-southeast-1.amazonaws.com/sleep-app-thesis/hardRefresh.gif)

### 6. Refresh All

-   Retrieve all data from Kubernetes
    ![Refresh All](https://alfa-cdn-us.s3.ap-southeast-1.amazonaws.com/sleep-app-thesis/refreshAll.gif)

### 7. Popup

-   Quickly View Resources State
    ![Popup](https://alfa-cdn-us.s3.ap-southeast-1.amazonaws.com/sleep-app-thesis/popup.gif)

## Auto Sleep Algorithm

-   File: `/app/Console/Commands/auto_sleep.php`
-   Runs hourly via ` $schedule->command('auto_sleep')->hourly();`
-   Algorithm:

    -   Retrieve active projects for auto sleep
    -   Stop if no active projects found
    -   Get all subservices grouped by project
    -   Prepare update lists:
        -   `subservicesToUpdate` (adjust `active_left`)
        -   `subservicesToSleep` (mark for sleep)
    -   Process each active project:
        -   Update project resources
        -   Process each subservice:
            -   If `active_left > 0`:
                -   If `active_left = 0`, mark for sleep
                -   Update `active_left`
    -   Update modified subservices
    -   Put marked subservices to sleep
    -   Complete the process

## Additional Information

### 1. Subservice is Optional

-   If not defined, Sleep App defaults to `projectName-no-subservice`.

### 2. Hard vs. Soft Refresh

-   **Soft Refresh:**
    -   Updates resource state without deleting database data.
    -   Runs in the background when:
        -   Opening popups
        -   Viewing resources
        -   Putting apps to sleep
-   **Hard Refresh:**
    -   Manually clears the database and fetches fresh data from Kubernetes.
    -   Use when database state differs from Kubernetes.

## Comparasion with Other Apps

### A. K9S

1. Can filter Deployment/StatefulSet by using ```-lproject=infra```

2. Still need to set replica 1 by 1

3. Sleep App streamlines this process with a single click at the Project or Subservice level
4. K9s necessitates custom RBAC configurations for developers, whereas Sleep App operates strictly at the Deployment/StatefulSets level, simplifying permissions management.

   ![K9S](https://alfa-cdn-us.s3.ap-southeast-1.amazonaws.com/sleep-app-thesis/k9s.gif)

### B. Kubectl

1. Requires familiarity with CLI commands.

2. Replicas must be set individually for each resource.

3. Sleep App streamlines this process with a single click at the Project or Subservice level, offering an intuitive and user-friendly interface.

4. Kubectl necessitates custom RBAC configurations for developers, whereas Sleep App operates strictly at the Deployment/StatefulSets level, simplifying permissions management.

    ![Kubectl](https://alfa-cdn-us.s3.ap-southeast-1.amazonaws.com/sleep-app-thesis/kubectl.gif)
