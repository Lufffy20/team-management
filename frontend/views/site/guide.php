<?php
use yii\helpers\Html;

$this->title = 'App Workflow Guide - How to Work';
?>
<style>
    /* Base Variables & Settings */
    :root {
        --primary-gradient: linear-gradient(135deg, #4b6cb7 0%, #182848 100%);
        --ind-gradient: linear-gradient(135deg, #11998e 0%, #38ef7d 100%);
        --team-gradient: linear-gradient(135deg, #FF416C 0%, #FF4B2B 100%);
    }

    /* Header Styling */
    .guide-header {
        background: var(--primary-gradient);
        color: white;
        padding: 2.5rem 1.5rem;
        border-radius: 12px;
        margin-bottom: 2rem;
        box-shadow: 0 8px 25px rgba(0,0,0,0.1);
        text-align: center;
    }
    
    .guide-header h1 {
        font-weight: 700;
        margin-bottom: 0.5rem;
        /* Scales smoothly between mobile and desktop */
        font-size: clamp(1.75rem, 4vw, 2.5rem); 
    }
    
    .guide-header p {
        font-size: clamp(0.9rem, 2vw, 1.1rem);
        opacity: 0.9;
        max-width: 600px;
        margin: 0 auto;
    }

    /* Card Styling */
    .workflow-card {
        background: #fff;
        border: none;
        border-radius: 12px;
        box-shadow: 0 4px 15px rgba(0,0,0,0.05);
        padding: 1.25rem;
        height: 100%;
        transition: transform 0.3s ease, box-shadow 0.3s ease;
    }

    @media (min-width: 768px) {
        .workflow-card {
            padding: 2rem;
        }
    }

    .workflow-card:hover {
        transform: translateY(-5px);
        box-shadow: 0 8px 25px rgba(0,0,0,0.1);
    }
    
    @media (max-width: 767px) {
        .workflow-card {
            margin-bottom: 1rem;
        }
        .timeline-badge {
            left: -15px;
            width: 20px;
            height: 20px;
        }
    }

    /* Card Header */
    .workflow-header {
        display: flex;
        align-items: center;
        margin-bottom: 1.5rem;
        padding-bottom: 1rem;
        border-bottom: 2px dashed #eee;
    }

    .workflow-icon {
        width: 45px;
        height: 45px;
        border-radius: 10px;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 1.25rem;
        color: white;
        margin-right: 15px;
        flex-shrink: 0;
    }

    .workflow-header h3 {
        font-size: clamp(1.15rem, 3vw, 1.5rem);
        font-weight: 700;
        margin-bottom: 0.2rem;
    }

    .icon-individual { background: var(--ind-gradient); }
    .icon-team { background: var(--team-gradient); }
    
    /* Timeline Design */
    .timeline {
        position: relative;
        padding-left: 20px;
    }

    @media (min-width: 768px) {
        .timeline {
            padding-left: 30px;
        }
    }

    .timeline::before {
        content: '';
        position: absolute;
        left: 8px;
        top: 0;
        bottom: 0;
        width: 2px;
        background: #e9ecef;
    }

    @media (min-width: 768px) {
        .timeline::before {
            left: 14px;
        }
    }

    .timeline-item {
        position: relative;
        margin-bottom: 1.25rem;
    }

    @media (min-width: 768px) {
        .timeline-item {
            margin-bottom: 1.5rem;
        }
    }

    .timeline-item:last-child {
        margin-bottom: 0;
    }
    
    .timeline-badge {
        position: absolute;
        left: -20px;
        top: 0;
        width: 20px;
        height: 20px;
        border-radius: 50%;
        background: #fff;
        border: 2px solid #007bff;
        display: flex;
        align-items: center;
        justify-content: center;
        font-weight: bold;
        color: #007bff;
        font-size: 0.75rem;
        z-index: 1;
        box-shadow: 0 0 0 4px #fff;
    }

    @media (min-width: 768px) {
        .timeline-badge {
            left: -30px;
            width: 30px;
            height: 30px;
            font-size: 0.85rem;
            top: -2px;
        }
    }

    .timeline-badge.team {
        border-color: #FF416C;
        color: #FF416C;
    }
    
    .timeline-badge.individual {
        border-color: #11998e;
        color: #11998e;
    }
    
    .timeline-content {
        padding-left: 10px;
    }

    @media (min-width: 768px) {
        .timeline-content {
            padding-left: 15px;
        }
    }
    
    .timeline-content h5 {
        font-weight: 600;
        font-size: 1rem;
        margin-bottom: 0.25rem;
        color: #333;
    }

    @media (min-width: 768px) {
        .timeline-content h5 {
            font-size: 1.15rem;
        }
    }
    
    .timeline-content p {
        color: #6c757d;
        font-size: 0.85rem;
        margin-bottom: 0;
        line-height: 1.4;
    }

    @media (min-width: 768px) {
        .timeline-content p {
            font-size: 0.95rem;
        }
    }

    /* Container Spacing for Mobile */
    .guide-container {
        padding: 1rem 0.5rem;
        overflow-x: hidden;
    }
    @media (min-width: 768px) {
        .guide-container {
            padding: 1.5rem;
        }
    }
</style>

<div class="container-fluid guide-container">
    <!-- Header -->
    <div class="guide-header">
        <h1><i class="bi bi-compass"></i> App Workflow Guide</h1>
        <p>A simple step-by-step guide to help you manage your tasks efficiently, whether on mobile, tablet, or desktop.</p>
    </div>

    <!-- Responsive Grid -->
    <div class="row g-4 mb-4">
        <!-- Individual Workflow -->
        <div class="col-12 col-md-6 col-lg-6">
            <div class="workflow-card">
                <div class="workflow-header">
                    <div class="workflow-icon icon-individual">
                        <i class="bi bi-person"></i>
                    </div>
                    <div>
                        <h3 class="mb-0">Individual Work</h3>
                        <p class="text-muted small mb-0">For personal task management</p>
                    </div>
                </div>

                <div class="timeline">
                    <div class="timeline-item">
                        <div class="timeline-badge individual">1</div>
                        <div class="timeline-content">
                            <h5>Create a Task</h5>
                            <p>Go to <strong>All Tasks</strong> or <strong>My Tasks</strong> and create a new task that you want to track.</p>
                        </div>
                    </div>
                    <div class="timeline-item">
                        <div class="timeline-badge individual">2</div>
                        <div class="timeline-content">
                            <h5>Manage in Kanban</h5>
                            <p>Switch to the <strong>Kanban Board</strong> to visually track your task's progress from Todo to Done.</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Team Workflow -->
        <div class="col-12 col-md-6 col-lg-6">
            <div class="workflow-card">
                <div class="workflow-header">
                    <div class="workflow-icon icon-team">
                        <i class="bi bi-people"></i>
                    </div>
                    <div>
                        <h3 class="mb-0">Team Collaboration</h3>
                        <p class="text-muted small mb-0">For projects involving multiple members</p>
                    </div>
                </div>

                <div class="timeline">
                    <div class="timeline-item">
                        <div class="timeline-badge team">1</div>
                        <div class="timeline-content">
                            <h5>Create a Project (Board)</h5>
                            <p>Start by creating a new <strong>Project</strong> space where your team's tasks will reside.</p>
                        </div>
                    </div>
                    <div class="timeline-item">
                        <div class="timeline-badge team">2</div>
                        <div class="timeline-content">
                            <h5>Create a Team</h5>
                            <p>Head to the <strong>Teams</strong> section and create a new team for your project.</p>
                        </div>
                    </div>
                    <div class="timeline-item">
                        <div class="timeline-badge team">3</div>
                        <div class="timeline-content">
                            <h5>Assign Kanban Space</h5>
                            <p>Enable and assign a dedicated <strong>Kanban Board</strong> space to the created team.</p>
                        </div>
                    </div>
                    <div class="timeline-item">
                        <div class="timeline-badge team">4</div>
                        <div class="timeline-content">
                            <h5>Assign Team to Board</h5>
                            <p>Link the team to the <strong>Project (Board)</strong> so everyone has access to the task list.</p>
                        </div>
                    </div>
                    <div class="timeline-item">
                        <div class="timeline-badge team">5</div>
                        <div class="timeline-content">
                            <h5>Add Team Members</h5>
                            <p>Finally, invite members to the team so they can start collaborating and picking up tasks.</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
