@extends('layouts.elcom')

@section('title', 'Real-Time Election Results - ' . $election->title)

@section('styles')
<style>
    .stats-card {
        background: white;
        border-radius: 8px;
        box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        transition: all 0.3s ease;
    }
    .stats-card:hover {
        transform: translateY(-2px);
        box-shadow: 0 4px 8px rgba(0,0,0,0.15);
    }
    .progress {
        height: 8px;
        border-radius: 4px;
    }
    .candidate-card {
        border-left: 4px solid #e9ecef;
        transition: all 0.3s ease;
    }
    .candidate-card:hover {
        border-left-color: #007bff;
        background-color: #f8f9fa;
    }
    .winner-badge {
        position: absolute;
        top: -10px;
        right: -10px;
        background: #28a745;
        color: white;
        padding: 4px 8px;
        border-radius: 12px;
        font-size: 0.75rem;
    }
    .refresh-indicator {
        font-size: 0.875rem;
        color: #6c757d;
    }
    @keyframes pulse {
        0% { opacity: 1; }
        50% { opacity: 0.5; }
        100% { opacity: 1; }
    }
    .live-indicator {
        animation: pulse 2s infinite;
    }
</style>
@endsection

@section('content')
<div class="container-fluid py-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1 class="h3 mb-0">Real-Time Election Results</h1>
        <div class="d-flex align-items-center">
            <span class="badge bg-success me-2">
                <i class="fas fa-circle live-indicator"></i> LIVE
            </span>
            <span class="refresh-indicator" id="lastUpdate">Connected</span>
        </div>
    </div>

    <div class="row mb-4">
        <div class="col-md-3">
            <div class="stats-card p-3">
                <h6 class="text-muted mb-2">Total Accredited Voters</h6>
                <h3 class="mb-0" id="totalAccredited">-</h3>
            </div>
        </div>
        <div class="col-md-3">
            <div class="stats-card p-3">
                <h6 class="text-muted mb-2">Total Votes Cast</h6>
                <h3 class="mb-0" id="totalVotes">-</h3>
            </div>
        </div>
        <div class="col-md-3">
            <div class="stats-card p-3">
                <h6 class="text-muted mb-2">Voter Turnout</h6>
                <h3 class="mb-0" id="voterTurnout">-</h3>
            </div>
        </div>
        <div class="col-md-3">
            <div class="stats-card p-3">
                <h6 class="text-muted mb-2">Time Remaining</h6>
                <h3 class="mb-0" id="timeRemaining">-</h3>
            </div>
        </div>
    </div>

    <div class="row" id="officesContainer">
        <!-- Office results will be dynamically inserted here -->
    </div>
</div>

<!-- Office Results Template -->
<template id="officeTemplate">
    <div class="col-md-6 mb-4">
        <div class="card">
            <div class="card-header">
                <h5 class="card-title mb-0 office-title"></h5>
            </div>
            <div class="card-body">
                <div class="candidates-list">
                    <!-- Candidate results will be inserted here -->
                </div>
            </div>
        </div>
    </div>
</template>

<!-- Candidate Template -->
<template id="candidateTemplate">
    <div class="candidate-card p-3 mb-3 position-relative">
        <div class="d-flex justify-content-between align-items-center mb-2">
            <h6 class="mb-0 candidate-name"></h6>
            <div class="d-flex align-items-center">
                <span class="votes-count me-2"></span>
                <span class="votes-percentage"></span>
            </div>
        </div>
        <div class="progress">
            <div class="progress-bar" role="progressbar" style="width: 0%"></div>
        </div>
    </div>
</template>
@endsection

@section('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    const officeTemplate = document.getElementById('officeTemplate');
    const candidateTemplate = document.getElementById('candidateTemplate');
    const officesContainer = document.getElementById('officesContainer');
    let lastUpdate = new Date();

    function formatNumber(num) {
        return new Intl.NumberFormat().format(num);
    }

    function updateStats(data) {
        document.getElementById('totalAccredited').textContent = formatNumber(data.totalAccredited);
        document.getElementById('totalVotes').textContent = formatNumber(data.totalVotes);
        document.getElementById('voterTurnout').textContent = `${data.voterTurnout}%`;
        document.getElementById('timeRemaining').textContent = data.timeRemaining;
    }

    function updateOfficeResults(data) {
        officesContainer.innerHTML = '';
        
        data.offices.forEach(office => {
            const officeNode = officeTemplate.content.cloneNode(true);
            const officeElement = officeNode.querySelector('.col-md-6');
            const candidatesList = officeNode.querySelector('.candidates-list');
            
            officeNode.querySelector('.office-title').textContent = office.title;
            
            office.candidates.forEach(candidate => {
                const candidateNode = candidateTemplate.content.cloneNode(true);
                const candidateElement = candidateNode.querySelector('.candidate-card');
                
                candidateNode.querySelector('.candidate-name').textContent = candidate.name;
                candidateNode.querySelector('.votes-count').textContent = `${formatNumber(candidate.votes)} votes`;
                candidateNode.querySelector('.votes-percentage').textContent = `${candidate.percentage.toFixed(1)}%`;
                
                const progressBar = candidateNode.querySelector('.progress-bar');
                progressBar.style.width = `${candidate.percentage}%`;
                
                // Add winner badge if this is the leading candidate
                if (candidate === office.candidates[0]) {
                    const badge = document.createElement('div');
                    badge.className = 'winner-badge';
                    badge.textContent = 'Leading';
                    candidateElement.appendChild(badge);
                }
                
                candidatesList.appendChild(candidateElement);
            });
            
            officesContainer.appendChild(officeElement);
        });
    }

    // Set up SSE connection
    const eventSource = new EventSource(`{{ route('elcom.elections.stream-results', $election) }}`);
    
    eventSource.onmessage = function(event) {
        const data = JSON.parse(event.data);
        updateStats(data);
        updateOfficeResults(data);
        lastUpdate = new Date();
        document.getElementById('lastUpdate').textContent = 'Connected';
    };

    eventSource.onerror = function(error) {
        console.error('SSE Error:', error);
        document.getElementById('lastUpdate').textContent = 'Connection lost. Reconnecting...';
        eventSource.close();
        // Attempt to reconnect after 5 seconds
        setTimeout(() => {
            window.location.reload();
        }, 5000);
    };

    // Update last update time every second
    setInterval(() => {
        const now = new Date();
        const diff = Math.floor((now - lastUpdate) / 1000);
        if (diff > 0) {
            document.getElementById('lastUpdate').textContent = `Last updated ${diff} seconds ago`;
        }
    }, 1000);
});
</script>
@endsection 