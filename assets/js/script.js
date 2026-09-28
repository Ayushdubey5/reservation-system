// assets/js/script.js

document.addEventListener('DOMContentLoaded', function() {

    // =====================================================
    // AUTO-DISMISS ALERTS
    // =====================================================
    const alerts = document.querySelectorAll('.alert');
    alerts.forEach(function(alert) {
        setTimeout(function() {
            alert.style.transition = 'opacity 0.5s';
            alert.style.opacity = '0';
            setTimeout(function() { alert.remove(); }, 500);
        }, 5000);
    });

    // =====================================================
    // CONFIRM DELETE
    // =====================================================
    document.querySelectorAll('.btn-delete').forEach(function(btn) {
        btn.addEventListener('click', function(e) {
            if (!confirm('Are you sure you want to delete this record? This action cannot be undone.')) {
                e.preventDefault();
            }
        });
    });

    // =====================================================
    // DYNAMIC PASSENGER ROWS
    // =====================================================
    const addPassengerBtn = document.getElementById('addPassenger');
    if (addPassengerBtn) {
        addPassengerBtn.addEventListener('click', function() {
            const tbody = document.getElementById('passengerBody');
            const rowCount = tbody.querySelectorAll('tr').length + 1;
            const row = createPassengerRow(rowCount);
            tbody.insertAdjacentHTML('beforeend', row);
            updatePassengerCount();
        });
    }

    // Remove passenger row
    document.addEventListener('click', function(e) {
        if (e.target.classList.contains('remove-passenger') || e.target.closest('.remove-passenger')) {
            const btn = e.target.classList.contains('remove-passenger') ? e.target : e.target.closest('.remove-passenger');
            const tbody = document.getElementById('passengerBody');
            if (tbody.querySelectorAll('tr').length > 1) {
                btn.closest('tr').remove();
                updatePassengerCount();
                renumberRows();
            } else {
                alert('At least one passenger is required.');
            }
        }
    });

    // =====================================================
    // COMMISSION CALCULATION
    // =====================================================
    const purchaseCostInput = document.getElementById('purchase_cost_display');
    const commissionPctInput = document.getElementById('commission_pct');
    const commissionAmtInput = document.getElementById('commission');
    const salesCostInput = document.getElementById('sales_cost');

    if (purchaseCostInput && commissionPctInput) {
        [commissionPctInput].forEach(function(input) {
            input.addEventListener('input', calculateCommission);
        });
    }

    function calculateCommission() {
        const cost = parseFloat(purchaseCostInput.value) || 0;
        const pct = parseFloat(commissionPctInput.value) || 0;
        const commission = (cost * pct) / 100;
        const salesCost = cost + commission;

        if (commissionAmtInput) commissionAmtInput.value = commission.toFixed(2);
        if (salesCostInput) salesCostInput.value = salesCost.toFixed(2);
    }

    // =====================================================
    // FETCH ORDER DETAILS FOR PURCHASE
    // =====================================================
    const orderSelect = document.getElementById('order_id');
    if (orderSelect && document.getElementById('purchase_form')) {
        orderSelect.addEventListener('change', function() {
            const orderId = this.value;
            if (orderId) {
                fetch('ajax_get_order.php?order_id=' + orderId)
                    .then(response => response.json())
                    .then(data => {
                        if (data.success) {
                            document.getElementById('source').value = data.order.source || '';
                            document.getElementById('destination').value = data.order.destination || '';
                            document.getElementById('mode').value = data.order.mode || '';
                            document.getElementById('journey_date').value = data.order.journey_date || '';

                            const tbody = document.getElementById('passengerBody');
                            tbody.innerHTML = '';
                            if (data.passengers && data.passengers.length > 0) {
                                data.passengers.forEach(function(p, idx) {
                                    tbody.insertAdjacentHTML('beforeend', createPassengerRowFilled(idx + 1, p));
                                });
                            }
                            updatePassengerCount();
                        }
                    })
                    .catch(err => console.error('Error:', err));
            }
        });
    }

    // =====================================================
    // FETCH PURCHASE DETAILS FOR SALE
    // =====================================================
    const purchaseSelect = document.getElementById('purchase_id');
    if (purchaseSelect && document.getElementById('sale_form')) {
        purchaseSelect.addEventListener('change', function() {
            const purchaseId = this.value;
            if (purchaseId) {
                fetch('ajax_get_purchase.php?purchase_id=' + purchaseId)
                    .then(response => response.json())
                    .then(data => {
                        if (data.success) {
                            document.getElementById('order_id_display').value = data.purchase.order_id || '';
                            document.getElementById('customer_display').value = data.purchase.customer_name || '';
                            document.getElementById('purchase_cost_display').value = data.purchase.purchase_cost || '';
                            document.getElementById('vendor_display').value = data.purchase.vendor_name || '';
                            document.getElementById('journey_display').value =
                                (data.purchase.source || '') + ' → ' + (data.purchase.destination || '') +
                                ' (' + (data.purchase.mode || '') + ') on ' + (data.purchase.journey_date || '');
                            document.getElementById('pnr_display').value = data.purchase.ticket_pnr || '';

                            // hidden fields
                            document.getElementById('order_id').value = data.purchase.order_id || '';
                            document.getElementById('customer_id').value = data.purchase.customer_id || '';

                            calculateCommission();
                        }
                    })
                    .catch(err => console.error('Error:', err));
            }
        });
    }

});

// =====================================================
// HELPER FUNCTIONS
// =====================================================
function createPassengerRow(num) {
    return `<tr>
        <td>${num}</td>
        <td><input type="text" name="p_name[]" required></td>
        <td><input type="number" name="p_age[]" min="1" max="120" required style="width:60px"></td>
        <td><select name="p_sex[]">
            <option value="Male">Male</option>
            <option value="Female">Female</option>
            <option value="Other">Other</option>
        </select></td>
        <td><input type="text" name="p_seat[]" style="width:70px"></td>
        <td><select name="p_food[]">
            <option value="No Food">No Food</option>
            <option value="Veg">Veg</option>
            <option value="Non-Veg">Non-Veg</option>
        </select></td>
        <td><select name="p_senior[]">
            <option value="No">No</option>
            <option value="Yes">Yes</option>
        </select></td>
        <td><select name="p_doctor[]">
            <option value="No">No</option>
            <option value="Yes">Yes</option>
        </select></td>
        <td><button type="button" class="btn btn-sm btn-danger remove-passenger">✕</button></td>
    </tr>`;
}

function createPassengerRowFilled(num, p) {
    return `<tr>
        <td>${num}</td>
        <td><input type="text" name="p_name[]" value="${p.passenger_name || ''}" required></td>
        <td><input type="number" name="p_age[]" value="${p.age || ''}" min="1" max="120" required style="width:60px"></td>
        <td><select name="p_sex[]">
            <option value="Male" ${p.sex === 'Male' ? 'selected' : ''}>Male</option>
            <option value="Female" ${p.sex === 'Female' ? 'selected' : ''}>Female</option>
            <option value="Other" ${p.sex === 'Other' ? 'selected' : ''}>Other</option>
        </select></td>
        <td><input type="text" name="p_seat[]" value="${p.seat || ''}" style="width:70px"></td>
        <td><select name="p_food[]">
            <option value="No Food" ${p.food_preference === 'No Food' ? 'selected' : ''}>No Food</option>
            <option value="Veg" ${p.food_preference === 'Veg' ? 'selected' : ''}>Veg</option>
            <option value="Non-Veg" ${p.food_preference === 'Non-Veg' ? 'selected' : ''}>Non-Veg</option>
        </select></td>
        <td><select name="p_senior[]">
            <option value="No" ${p.senior_citizen === 'No' ? 'selected' : ''}>No</option>
            <option value="Yes" ${p.senior_citizen === 'Yes' ? 'selected' : ''}>Yes</option>
        </select></td>
        <td><select name="p_doctor[]">
            <option value="No" ${p.doctor === 'No' ? 'selected' : ''}>No</option>
            <option value="Yes" ${p.doctor === 'Yes' ? 'selected' : ''}>Yes</option>
        </select></td>
        <td><button type="button" class="btn btn-sm btn-danger remove-passenger">✕</button></td>
    </tr>`;
}

function updatePassengerCount() {
    const count = document.querySelectorAll('#passengerBody tr').length;
    const pCountInput = document.getElementById('passenger_count');
    if (pCountInput) pCountInput.value = count;
}

function renumberRows() {
    const rows = document.querySelectorAll('#passengerBody tr');
    rows.forEach(function(row, idx) {
        row.querySelector('td:first-child').textContent = idx + 1;
    });
}