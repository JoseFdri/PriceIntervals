<!doctype html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport"
          content="width=device-width, user-scalable=no, initial-scale=1.0, maximum-scale=1.0, minimum-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.3.1/css/bootstrap.min.css" integrity="sha384-ggOyR0iXCbMQv3Xipma34MD+dH/1fQ784/j6cY/iJTQUOhcWr7x9JvoRxT2MZw1T" crossorigin="anonymous">
    <link rel="stylesheet" href="assets/css/index.css">
    <title>Price Intervals</title>
</head>
<body>
    <div class="container" id="app">
        <div class="alert mt-3" :class="alert.type" role="alert" id="alert" style="opactiy: 0; z-index: 999999; position:relative">
            {{alert.message}}
        </div>
        <div class="row mt-5 mb-3">
            <div class="col-md-12 text-center">

            </div>
        </div>
        <div class="row">
            <div class="col-md-12 d-flex justify-content-between align-items-center mb-3">
                <div>
                    <h2>Price intervals</h2>
                </div>
                <div>
                    <button type="button"
                            class="btn btn-success"
                            v-on:click="addNewInterval">
                        Create
                    </button>
                    <button type="button"
                            class="btn btn-warning ml-1"
                            v-on:click="reset">
                        Reset
                    </button>
                </div>
            </div>
        </div>
        <div class="row">
            <div class="col-md-12">
                <table class="table table-dark">
                    <thead>
                    <tr>
                        <th scope="col">Id</th>
                        <th scope="col">Start Date</th>
                        <th scope="col">End Date</th>
                        <th scope="col">Price</th>
                        <th scope="col">Action</th>
                    </tr>
                    </thead>
                    <tbody v-if="priceIntervals.length > 0">
                        <tr v-for="priceInterval in priceIntervals">
                            <th scope="row">{{priceInterval.id}}</th>
                            <td>{{priceInterval.date_start}}</td>
                            <td>{{priceInterval.date_end}}</td>
                            <td>{{priceInterval.price}}</td>
                            <td>
                                <button type="button"
                                        class="btn btn-info"
                                        v-on:click="editInterval(priceInterval)">
                                    Edit
                                </button>
                                <button type="button"
                                        class="btn btn-danger"
                                        v-on:click="deleteInterval(priceInterval)">
                                    Delete
                                </button>
                            </td>
                        </tr>
                    </tbody>
                </table>

            </div>
        </div>
        <div class="modal fade" tabindex="-1" role="dialog" id="priceIntervalModal">
            <div class="modal-dialog modal-dialog-centered" role="document">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title">{{modal.title}}</h5>
                        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                            <span aria-hidden="true">&times;</span>
                        </button>
                    </div>
                    <div class="modal-body">
                        <form>
                            <div class="form-group">
                                <label for="exampleInputEmail1">Start date</label>
                                <input type="date" class="form-control" v-model="modal.startDate">
                            </div>
                            <div class="form-group">
                                <label for="exampleInputEmail1">End date</label>
                                <input type="date" class="form-control" v-model="modal.endDate">
                            </div>
                            <div class="form-group">
                                <label for="exampleInputEmail1">Price</label>
                                <input type="number" class="form-control" v-model="modal.price">
                            </div>
                        </form>
                    </div>
                    <div class="modal-footer">
                        <button type="button"
                                class="btn btn-secondary" d
                                data-dismiss="modal">
                            Close
                        </button>
                        <button type="button"
                                class="btn btn-primary"
                                v-on:click="performModalAction">
                            {{modal.saveButtonText}}
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <script src="https://code.jquery.com/jquery-3.3.1.slim.min.js" integrity="sha384-q8i/X+965DzO0rT7abK41JStQIAqVgRVzpbzo5smXKp4YfRvH+8abtTE1Pi6jizo" crossorigin="anonymous"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/popper.js/1.14.7/umd/popper.min.js" integrity="sha384-UO2eT0CpHqdSJQ6hJty5KVphtPhzWj9WO1clHTMGa3JDZwrnQq4sF86dIHNDz0W1" crossorigin="anonymous"></script>
    <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.3.1/js/bootstrap.min.js" integrity="sha384-JjSmVgyd0p3pXB1rRibZUAYoIIy6OrQ6VrjIEaFf/nJGzIxFDsf4x0xIM+B07jRM" crossorigin="anonymous"></script>
    <script src="https://cdn.jsdelivr.net/npm/vue/dist/vue.js"></script>
    <script src="assets/js/moment.js"></script>
    <script src="assets/js/index.js"></script>
</body>
</html>