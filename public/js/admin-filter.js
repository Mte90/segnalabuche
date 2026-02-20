window.adminStatusLabels = {
    '': 'Tutte le segnalazioni',
    'all': 'Tutte le segnalazioni',
    'pending': 'Segnalazioni in sospeso',
    'approved': 'Segnalazioni approvate',
    'rejected': 'Segnalazioni rifiutate'
};

window.getStatusLabel = function(status) {
    return window.adminStatusLabels[status] || 'Tutte le segnalazioni';
};
