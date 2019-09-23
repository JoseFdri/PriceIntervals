app = new Vue({
    el: '#app',
    data: {
        priceIntervals: [],
        modal: {
            title: '',
            saveButtonText: '',
            startDate: moment().format('YYYY-MM-D'),
            endDate:  moment().format('YYYY-MM-D'),
            price: 0,
            action: '',
            id: 0
        },
        alert: {
            message: '',
            type: ''
        }
    },
    methods: {
        getAllPriceIntervals: function(){
            fetch('./priceInterval/all')
                .then(function (response){
                    response.json().then(function(data) {
                        app.priceIntervals = data;
                    });
                })
        },
        addNewInterval: function (event) {
            app.modal.title = 'Create Price Interval';
            app.modal.saveButtonText = 'Create';
            app.modal.action = 'create';
            $('#priceIntervalModal').modal('show');
        },
        editInterval: function (priceInterval) {
            app.modal.title = 'Edit Price Interval';
            app.modal.saveButtonText = 'Save';
            app.modal.action = 'edit';
            app.modal.startDate = priceInterval.date_start;
            app.modal.endDate = priceInterval.date_end;
            app.modal.price = priceInterval.price;
            app.modal.id = priceInterval.id;
            $('#priceIntervalModal').modal('show');
        },
        performModalAction: function (){
            if(app.modal.action === 'create') {
                app.sendNewInterval();
            }else if (app.modal.action === 'edit') {
                app.sendEditInterval();
            }
        },
        sendNewInterval: function (){
            let data = {
                date_start: app.modal.startDate || moment().format('YYYY-MM-D'),
                date_end: app.modal.endDate || moment().format('YYYY-MM-D'),
                price: app.modal.price || 0
            };
            fetch('./priceInterval/insert', {
                method: 'POST',
                body: JSON.stringify(data),
                headers:{
                    'Content-Type': 'application/json'
                }
            }).then(res => {
                res.json().then(function(rsp) {
                    if(rsp.status == 1) {
                        $('#priceIntervalModal').modal('hide');
                        app.showAlert('alert-success', rsp.message);
                        app.getAllPriceIntervals();
                    }else {
                        app.showAlert('alert-danger', rsp.message);
                    }
                });
            })
            .catch(error => console.error('Error:', error))
        },
        sendEditInterval: function (){
            let data = {
                date_start: app.modal.startDate,
                date_end: app.modal.endDate,
                price: app.modal.price || 0,
                id: app.modal.id
            };
            fetch('./priceInterval/update', {
                method: 'PUT',
                body: JSON.stringify(data),
                headers:{
                    'Content-Type': 'application/json'
                }
            }).then(res => {
                res.json().then(function(rsp) {
                    if(rsp.status == 1) {
                        $('#priceIntervalModal').modal('hide');
                        app.showAlert('alert-success', rsp.message);
                        app.getAllPriceIntervals();
                    }else {
                        app.showAlert('alert-danger', rsp.message);
                    }
                });
            })
            .catch(error => console.error('Error:', error))
        },
        deleteInterval: function (interval) {
            fetch('./priceInterval/delete/'+interval.id, {
                method: 'DELETE',
            }).then(res => {
                res.json().then(function(rsp) {
                    if(rsp.status === 1) {
                        app.showAlert('alert-success', rsp.message);
                        app.getAllPriceIntervals();
                    }else {
                        app.showAlert('alert-danger', rsp.message);
                    }
                });
            })
            .catch(error => console.error('Error:', error))
            .then(response => console.log('Success:', response));
        },
        reset: function() {
            fetch('./priceInterval/reset', {
                method: 'DELETE',
            }).then(res => {
                res.json().then(function(rsp) {
                    if(rsp.status === 1) {
                        app.showAlert('alert-success', rsp.message);
                        app.getAllPriceIntervals();
                    }else {
                        app.showAlert('alert-danger', rsp.message);
                    }
                });
            })
            .catch(error => console.error('Error:', error))
        },
        showAlert: function (type, message) {
            app.alert.type = type;
            app.alert.message = message;
            $('#alert').css({opacity: 1, transition: 'all 500ms'});
            setTimeout(function(){
                $('#alert').css({opacity: 0, transition: 'all 500ms'});
            }, 2000);
        },
    }
});

app.getAllPriceIntervals()