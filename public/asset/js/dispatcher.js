'use strict';

class DispatcherPanel extends React.Component {
    componentWillMount() {
        this.setState({
            listContent: 'dispatch-map'
        });
    }

    handleUpdateBody(body) {
        console.log('Body Update Called', body);
        this.setState({
            listContent: body
        });
    }

    handleUpdateFilter(filter) {
        console.log('Filter Update Called', this.state.listContent);
        if (filter == 'all') {
            this.setState({
                listContent: 'dispatch-map'
            });
        }
        else if (filter == 'cancelled') {
            this.setState({
                listContent: 'cancelled'
            });
        }
        else if (filter == 'searching') {
            this.setState({
                listContent: 'searching'
            });
        }
        else if (filter == 'online') {
            this.setState({
                listContent: 'online'
            });
        }
        else if (filter == 'offline') {
            this.setState({
                listContent: 'offline'
            });
        }
        else if (filter == 'return') {
            this.setState({
                listContent: 'dispatch-return'
            });
        }
        /////////////
        else if (filter == 'arrived') {
            this.setState({
                listContent: 'arrived'
            });
        }
        else if (filter == 'pickup') {
            this.setState({
                listContent: 'pickup'
            });
        }
        else if (filter == 'dropped') {
            this.setState({
                listContent: 'dropped'
            });
        }
        else if (filter == 'completed') {
            this.setState({
                listContent: 'completed'
            });
        }
        else if (filter == 'accepted') {
            this.setState({
                listContent: 'accepted'
            });
        }
        //////////
        else {
            this.setState({
                listContent: 'dispatch-map'
            });
        }


        // this.setState({
        //     listContent: 'dispatch-map'
        // });
    }

    handleRequestShow(trip) {
        // console.log('Show Request', trip);
        if (trip.current_provider_id == 0) {
            this.setState({
                listContent: 'dispatch-assign',
                trip: trip
            });
        } else {
            this.setState({
                listContent: 'dispatch-map',
                trip: trip
            });
        }
        ongoingInitialize(trip);
    }

    handleRequestCancel(argument) {
        this.setState({
            listContent: 'dispatch-map'
        });
    }

    render() {

        let listContent = null;

        // console.log('DispatcherPanel', this.state.listContent);

        switch (this.state.listContent) {
            case 'dispatch-create':
                listContent = <div className="col-md-4">
                    <DispatcherRequest completed={this.handleRequestShow.bind(this)} cancel={this.handleRequestCancel.bind(this)} />
                </div>;
                break;
            case 'searching':
                listContent = <div className="col-md-4">
                    <DispatcherSearchList clicked={this.handleRequestShow.bind(this)} />
                </div>;
                break;
            case 'cancelled':
                listContent = <div className="col-md-4">
                    <DispatcherCancelledList clicked={this.handleRequestShow.bind(this)} />
                </div>;
                break;
            case 'dispatch-map':
                listContent = <div className="col-md-4">
                    <DispatcherList clicked={this.handleRequestShow.bind(this)} />
                </div>;
                break;
            case 'dispatch-assign':
                listContent = <div className="col-md-4">
                    <DispatcherAssignList trip={this.state.trip} />
                </div>;
                break;
            case 'online':
                listContent = <div className="col-md-4">
                    <DispatcherOnlineList trip={this.state.trip} />
                </div>;
                break;
            case 'offline':
                listContent = <div className="col-md-4">
                    <DispatcherOfflineList trip={this.state.trip} />
                </div>;
                break;
            case 'completed':
                listContent = <div className="col-md-4">
                    <DispatcherCompletedList trip={this.state.trip} />
                </div>;
                break;
            case 'dropped':
                listContent = <div className="col-md-4">
                    <DispatcherDroppedList trip={this.state.trip} />
                </div>;
                break;
            case 'pickup':
                listContent = <div className="col-md-4">
                    <DispatcherPickupList trip={this.state.trip} />
                </div>;
                break;
            case 'arrived':
                listContent = <div className="col-md-4">
                    <DispatcherArrivedList trip={this.state.trip} />
                </div>;
                break;
            case 'accepted':
                listContent = <div className="col-md-4">
                    <DispatcherAcceptedList trip={this.state.trip} />
                </div>;
                break;
        }

        return (
            <div className="container-fluid">
                <h4>Dispatcher</h4>

                <DispatcherNavbar body={this.state.listContent} updateBody={this.handleUpdateBody.bind(this)} updateFilter={this.handleUpdateFilter.bind(this)} />

                <div className="row">
                    {listContent}

                    <div className="col-md-8">
                        <DispatcherMap body={this.state.listContent} />
                    </div>
                </div>
            </div>
        );

    }
};

class DispatcherNavbar extends React.Component {

    constructor(props) {
        super(props);
        this.state = {
            body: 'dispatch-map',
            selected: ''
        };
    }

    filter(data) {
        console.log('Navbar Filter', data);
        this.setState({ selected: data })
        this.props.updateFilter(data);
    }

    handleBodyChange() {
        // console.log('handleBodyChange', this.state);
        if (this.props.body != this.state.body) {
            this.setState({
                body: this.props.body
            });
        }

        if (this.state.body == 'dispatch-map') {
            this.props.updateBody('dispatch-create');
            this.setState({
                body: 'dispatch-create'
            });
        } else {
            this.props.updateBody('dispatch-map');
            this.setState({
                body: 'dispatch-map'
            });
        }
    }

    isActive(value) {
        return 'nav-item ' + ((value === this.state.selected) ? 'active' : '');
    }
    render() {
        return (
            <nav className="navbar navbar-light bg-white b-a mb-2">
                <button className="navbar-toggler hidden-md-up"
                    data-toggle="collapse"
                    data-target="#process-filters"
                    aria-controls="process-filters"
                    aria-expanded="false"
                    aria-label="Toggle Navigation"></button>

                <ul className="nav navbar-nav float-xs-right">
                    <li className="nav-item">
                        <button type="button"
                            onClick={this.handleBodyChange.bind(this)}
                            className="btn btn-success btn-md label-right b-a-0 waves-effect waves-light">
                            <span className="btn-label"><i className="ti-plus"></i></span>
                            ADD
                        </button>
                    </li>
                </ul>

                <div className="collapse navbar-toggleable-sm" id="process-filters">
                    <ul className="nav navbar-nav dispatcher-nav">
                        <li className="nav-item active" onClick={this.filter.bind(this, 'all')}>
                            <span className="nav-link" href="#">All</span>
                        </li>
                        <li className={this.isActive('searching')} onClick={this.filter.bind(this, 'searching')}>
                            <span className="nav-link" href="#">Searching</span>
                        </li>
                        <li className={this.isActive('cancelled')} onClick={this.filter.bind(this, 'cancelled')}>
                            <span className="nav-link" href="#">Cancelled</span>
                        </li>
                        {/* <li className={this.isActive('online')} onClick={this.filter.bind(this, 'online')}>
                            <span className="nav-link" href="#">Online</span>
                        </li>
                        <li className={this.isActive('offline')} onClick={this.filter.bind(this, 'offline')}>
                            <span className="nav-link" href="#">Offline</span>
                        </li> */}
                        <li className={this.isActive('accepted')} onClick={this.filter.bind(this, 'accepted')}>
                            <span className="nav-link" href="#">Accepted</span>
                        </li>
                        <li className={this.isActive('arrived')} onClick={this.filter.bind(this, 'arrived')}>
                            <span className="nav-link" href="#">Arrived</span>
                        </li>
                        <li className={this.isActive('pickup')} onClick={this.filter.bind(this, 'pickup')}>
                            <span className="nav-link" href="#">Pickup</span>
                        </li>
                        <li className={this.isActive('dropped')} onClick={this.filter.bind(this, 'dropped')}>
                            <span className="nav-link" href="#">Dropped</span>
                        </li>
                        <li className={this.isActive('completed')} onClick={this.filter.bind(this, 'completed')}>
                            <span className="nav-link" href="#">Completed</span>
                        </li>
                    </ul>
                </div>
            </nav>
        );
    }
}

class DispatcherList extends React.Component {
    constructor(props) {
        super(props);
        this.state = {
            data: {
                data: []
            }
        };
    }

    componentDidMount() {
        // Mount Global Map
        window.worldMapInitialize();
        this.getTripsUpdate();
        // Refresh trip details
        window.Tranxit.TripTimer = setInterval(
            () => this.getTripsUpdate(),
            50000
        );
    }

    componentWillUnmount() {
        clearInterval(window.Tranxit.TripTimer);
    }

    getTripsUpdate() {
        $.get('/dispatcher/dispatcher/trips', function (result) {
            // console.log('Trips', result.hasOwnProperty('data'));
            if (result.hasOwnProperty('data')) {
                this.setState({
                    data: result
                });
            } else {
                // Might wanna show an empty list when this happens
                this.setState({
                    data: {
                        data: []
                    }
                });
            }
        }.bind(this));
    }

    handleClick(trip) {
        //console.log(trip);
        // if(trip.status == 'CANCELLED'){
        //     return false;
        // }
        let filter = trip.status;
        if (filter == 'all') {
            this.setState({
                listContent: 'dispatch-map'
            });
        }
        else if (filter == 'cancelled') {
            this.setState({
                listContent: 'cancelled'
            });
        }
        else if (filter == 'searching') {
            this.setState({
                listContent: 'searching'
            });
        }
        else if (filter == 'online') {
            this.setState({
                listContent: 'online'
            });
        }
        else if (filter == 'offline') {
            this.setState({
                listContent: 'offline'
            });
        }
        else if (filter == 'return') {
            this.setState({
                listContent: 'dispatch-return'
            });
        }
        /////////////
        else if (filter == 'arrived') {
            this.setState({
                listContent: 'arrived'
            });
        }
        else if (filter == 'pickup') {
            this.setState({
                listContent: 'pickup'
            });
        }
        else if (filter == 'dropped') {
            this.setState({
                listContent: 'dropped'
            });
        }
        else if (filter == 'completed') {
            this.setState({
                listContent: 'completed'
            });
        }
        else if (filter == 'accepted') {
            this.setState({
                listContent: 'accepted'
            });
        }
        //////////
        else {
            this.setState({
                listContent: 'dispatch-map'
            });
        }
        this.props.clicked(trip);
    }

    render() {
        // console.log(this.state.data);
        return (
            <div className="card">
                <div className="card-header text-uppercase"><b>List</b></div>

                <DispatcherListItem data={this.state.data.data} clicked={this.handleClick.bind(this)} />
            </div>
        );
    }
}

class DispatcherListItem extends React.Component {
    handleClick(trip) {
        this.props.clicked(trip)
    }
    render() {
        var listItem = function (trip) {
            return (
                <div className="il-item" key={trip.id} onClick={this.handleClick.bind(this, trip)}>
                    <a className="text-black" href="#">
                        <div className="media">
                            <div className="media-body">
                                <p className="mb-0-5">{trip.user.first_name} {trip.user.last_name}
                                    {trip.status == 'COMPLETED' ?
                                        <span className="tag tag-success pull-right"> {trip.status} </span>
                                        : trip.status == 'CANCELLED' ?
                                            <span className="tag tag-danger pull-right"> {trip.status} </span>
                                            : trip.status == 'SEARCHING' ?
                                                <span className="tag tag-warning pull-right"> {trip.status} </span>
                                                : trip.status == 'SCHEDULED' ?
                                                    <span className="tag tag-primary pull-right"> {trip.status} </span>
                                                    :
                                                    <span className="tag tag-info pull-right"> {trip.status} </span>
                                    }
                                </p>
                                <h6 className="media-heading">From: {trip.s_address}</h6>
                                <h6 className="media-heading">To: {trip.d_address ? trip.d_address : "Not Selected"}</h6>
                                <div style={{display:'flex',}}>    
                                    <h6 className="media-heading">Payment: {trip.payment_mode}</h6>
                                    {trip.provider
                                        ? <h6 style={{paddingLeft: '20px'}} className="media-heading">Cab No: {trip.provider ? trip.provider.service ? trip.provider.service.service_number : '' : ''}</h6>
                                        :  ''
                                    }
                                    
                                </div>
                                <progress className="progress progress-success progress-sm" max="100"></progress>
                                <span className="text-muted">{trip.current_provider_id == 0 ? "Manual Assignment" : "Auto Search"} : {trip.created_at}</span>
                            </div>
                        </div>
                    </a>
                </div>
            );
        }.bind(this);

        return (
            <div className="items-list">
                {this.props.data.map(listItem)}
            </div>
        );
    }
}

class DispatcherRequest extends React.Component {

    constructor(props) {
        super(props);
        this.state = {
            data: [],
            discountAmount: 0,
            extraAmount: 0,
            totalAmount: 0,
            etaFare: 0
        };
    }

    componentDidMount() {

        // Auto Assign Switch
        new Switchery(document.getElementById('provider_auto_assign'));

        // Schedule Time Datepicker
        $('#schedule_time').datetimepicker({
            minDate: window.Tranxit.minDate,
            maxDate: window.Tranxit.maxDate,
            defaultDate: window.Tranxit.defaultDate
        });

        // Get Service Type List
        $.get('/dispatcher/service', function (result) {
            localStorage.serviceData = JSON.stringify(result);
            this.setState({
                data: result
            });
        }.bind(this));

        // Mount Ride Create Map

        window.createRideInitialize();

        function stopRKey(evt) {
            var evt = (evt) ? evt : ((event) ? event : null);
            var node = (evt.target) ? evt.target : ((evt.srcElement) ? evt.srcElement : null);
            if ((evt.keyCode == 13) && (node.type == "text")) { return false; }
        }

        document.onkeypress = stopRKey;
    }

    createRide(event) {
        console.log(event);
        event.preventDefault();
        event.stopPropagation();
        console.log('Hello', $("#form-create-ride").serialize());
        $.ajax({
            url: '/dispatcher/dispatcher',
            dataType: 'json',
            headers: { 'X-CSRF-TOKEN': window.Laravel.csrfToken },
            type: 'POST',
            data: $("#form-create-ride").serialize(),
            success: function (data) {
                this.props.completed(data);
            }.bind(this)
        });
    }

    cancelCreate() {
        this.props.cancel(true);
    }
    calculateETA(event) {
        console.log(event);
        event.preventDefault();
        event.stopPropagation();
        console.log('Hello', $("#form-create-ride").serialize());
        $.ajax({
            url: '/admin/dispatcher',
            dataType: 'json',
            headers: { 'X-CSRF-TOKEN': window.Laravel.csrfToken },
            type: 'POST',
            data: $("#form-create-ride").serialize(),
            success: function (data) {
                // $('#total_distance').html(data.totalDistance)
                this.props.completed(data);
            }.bind(this)
        });
    }
    calculateTotal() {
        var eta = $('#eta').val();
        var discount = $('#discount').val();
        var extraAmount = $('#extraAmount').val();

        eta = eta ? eta : 0;
        discount = discount ? discount : 0;
        extraAmount = extraAmount ? extraAmount : 0;

        var totalAmount = (parseInt(eta) + parseInt(extraAmount)) - parseInt(discount)
        $('#totalAmount').html(totalAmount);
    }

    render() {
        return (
            <div className="card card-block" id="create-ride">
                <h3 className="card-title text-uppercase">Ride Details</h3>
                <form id="form-create-ride" onSubmit={this.createRide.bind(this)} method="POST">
                    <div className="row">
                        <div className="col-xs-6">
                            <div className="form-group">
                                <label htmlFor="first_name">First Name</label>
                                <input type="text" className="form-control" name="first_name" id="first_name" placeholder="First Name" />
                            </div>
                        </div>
                        <div className="col-xs-6">
                            <div className="form-group">
                                <label htmlFor="last_name">Last Name</label>
                                <input type="text" className="form-control" name="last_name" id="last_name" placeholder="Last Name" />
                            </div>
                        </div>
                        <div className="col-xs-6">
                            <div className="form-group">
                                <label htmlFor="email">Email</label>
                                <input type="email" className="form-control" name="email" id="email" placeholder="Email" />
                            </div>
                        </div>
                        <div className="col-xs-6">
                            <div className="form-group">
                                <label htmlFor="mobile">Phone</label>
                                <input type="text" className="form-control" name="mobile" id="mobile" placeholder="Phone" required />
                            </div>
                        </div>
                        <div className="col-xs-12">
                            <div className="form-group">
                                <label htmlFor="s_address">Pickup Address</label>

                                <input type="text"
                                    name="s_address"
                                    className="form-control"
                                    id="s_address"
                                    placeholder="Pickup Address"
                                    required></input>

                                <input type="hidden" name="s_latitude" id="s_latitude"></input>
                                <input type="hidden" name="s_longitude" id="s_longitude"></input>
                            </div>
                            <div className="form-group">
                                <label htmlFor="d_address">Dropoff Address</label>

                                <input type="text"
                                    name="d_address"
                                    className="form-control"
                                    id="d_address"
                                    placeholder="Dropoff Address"
                                    required></input>

                                <input type="hidden" name="d_latitude" id="d_latitude"></input>
                                <input type="hidden" name="d_longitude" id="d_longitude"></input>
                                <input type="hidden" name="distance" id="distance"></input>
                            </div>
                            {/* <div className="form-group" >
                                <label htmlFor="schedule_time">Schedule Time</label>
                                <input type="text" className="form-control" name="schedule_time" id="schedule_time" placeholder="" required />
                            </div> */}
                            <div className="form-group">
                                <label htmlFor="service_types">Service Type</label>
                                <ServiceTypes data={this.state.data} />
                            </div>
                            <div className="form-group">
                                <label htmlFor="provider_auto_assign">Auto Assign Provider</label>
                                <br />
                                <input type="checkbox" id="provider_auto_assign" name="provider_auto_assign" className="js-switch" data-color="#f59345" defaultChecked />
                            </div>
                        </div>
                    </div>
                    <div className="row">
                        <div className="col-md-12">
                            <div className="form-group">
                                <table id="eta_table">
                                    <tr>
                                        <td>Total Distance: </td>
                                        <td id="total_distance"></td>
                                    </tr>
                                    <tr>
                                        <td>Estimated Time: </td>
                                        <td><input type="text" className="form-control" name="estimated_time" id="estimated_time" placeholder="Estimated Time" defaultValue="" /></td>
                                    </tr>
                                </table>
                                <label htmlFor="mobile">ETA Fare:</label>
                                <input type="text" className="form-control" name="eta" id="eta" placeholder="ETA Fare" defaultValue="" onChange={this.calculateTotal} />
                                <label htmlFor="mobile">Discount:</label>
                                <input type="text" className="form-control" name="discount" id="discount" placeholder="Discount" defaultValue="" onChange={this.calculateTotal} />
                                <label htmlFor="mobile">Extra:</label>
                                <input type="text" className="form-control" name="extraAmount" id="extraAmount" placeholder="Extra Amount" defaultValue="" onChange={this.calculateTotal} />
                                <label htmlFor="mobile"><b>Total:</b></label>
                                <p id="totalAmount"></p>
                            </div>
                        </div>
                    </div>
                    <div className="row">
                        <div className="col-xs-6">
                            <button type="button" className="btn btn-lg btn-danger btn-block waves-effect waves-light" onClick={this.cancelCreate.bind(this)}>
                                CANCEL
                            </button>
                        </div>
                        <div className="col-xs-6">
                            <button className="btn btn-lg btn-success btn-block waves-effect waves-light">
                                SUBMIT
                            </button>
                        </div>
                    </div>
                </form>
            </div>
        );
    }
};

class DispatcherAssignList extends React.Component {
    constructor(props) {
        super(props);
        this.state = {
            data: {
                data: []
            }
        };
    }

    componentDidMount() {
        $.get('/dispatcher/dispatcher/providers', {
            latitude: this.props.trip.s_latitude,
            longitude: this.props.trip.s_longitude,
            service_type: this.props.trip.service_type_id
        }, function (result) {
            console.log('Providers', result);
            if (result.hasOwnProperty('data')) {
                this.setState({
                    data: result
                });
                window.assignProviderShow(result.data, this.props.trip);
            } else {
                this.setState({
                    data: {
                        data: []
                    }
                });
                window.providerMarkersClear();
            }
        }.bind(this));
    }

    render() {
        console.log('DispatcherAssignList - render', this.state.data);
        return (
            <div className="card">
                <div className="card-header text-uppercase"><b>Assign Provider</b></div>

                <DispatcherAssignListItem data={this.state.data.data} trip={this.props.trip} />
            </div>
        );
    }
}

class DispatcherAssignListItem extends React.Component {
    handleClick(provider) {
        // this.props.clicked(trip)
        console.log('Provider Clicked');
        window.assignProviderPopPicked(provider);
    }
    render() {
        var listItem = function (provider) {
            return (
                <div className="il-item" key={provider.id} onClick={this.handleClick.bind(this, provider)}>
                    <a className="text-black" href="#">
                        <div className="media">
                            <div className="media-body">
                                <p className="mb-0-5">{provider.first_name} {provider.last_name}</p>
                                <h6 className="media-heading">Rating: {provider.rating}</h6>
                                <h6 className="media-heading">Phone: {provider.mobile}</h6>
                                <h6 className="media-heading">Type: {provider.service.service_type.name}</h6>
                            </div>
                        </div>
                    </a>
                </div>
            );
        }.bind(this);

        return (
            <div className="items-list">
                {this.props.data.map(listItem)}
            </div>
        );
    }
}

class ServiceTypes extends React.Component {
    render() {
        // console.log('ServiceTypes', this.props.data);
        var mySelectOptions = function (result) {
            return <ServiceTypesOption
                key={result.id}
                id={result.id}
                name={result.name} />
        };
        return (
            <select
                name="service_type"
                className="form-control">
                {this.props.data.map(mySelectOptions)}
            </select>
        )
    }
}

class ServiceTypesOption extends React.Component {
    render() {
        return (
            <option value={this.props.id}>{this.props.name}</option>
        );
    }
};

class DispatcherMap extends React.Component {
    render() {
        return (
            <div className="card my-card">
                <div className="card-header text-uppercase">
                    <b>MAP</b>
                </div>
                <div className="card-body">
                    <div id="map" style={{ height: '450px' }}></div>
                </div>
            </div>
        );
    }
}

class DispatcherSearchList extends React.Component {
    constructor(props) {
        super(props);
        this.state = {
            data: {
                data: []
            }
        };
    }

    componentDidMount() {
        window.worldMapInitialize();
        this.getTripsUpdate();
        window.Tranxit.TripTimer = setInterval(
            () => this.getTripsUpdate(),
            50000
        );
    }

    componentWillUnmount() {
        clearInterval(window.Tranxit.TripTimer);
    }

    getTripsUpdate() { //Changed API Required
        $.get(`/dispatcher/dispatcher/trips?type=SEARCHING`, function (result) {
            if (result.hasOwnProperty('data')) {
                this.setState({
                    data: result
                });
            } else {
                this.setState({
                    data: {
                        data: []
                    }
                });
            }
        }.bind(this));
    }

    // handleClick(trip) {
    //     this.props.clicked(trip);
    // }
    handleClick(trip) {
        //console.log(trip);
        // if(trip.status == 'CANCELLED'){
        //     return false;
        // }
        let filter = trip.status;
        if (filter == 'all') {
            this.setState({
                listContent: 'dispatch-map'
            });
        }
        else if (filter == 'cancelled') {
            this.setState({
                listContent: 'cancelled'
            });
        }
        else if (filter == 'searching') {
            this.setState({
                listContent: 'searching'
            });
        }
        else if (filter == 'online') {
            this.setState({
                listContent: 'online'
            });
        }
        else if (filter == 'offline') {
            this.setState({
                listContent: 'offline'
            });
        }
        else if (filter == 'return') {
            this.setState({
                listContent: 'dispatch-return'
            });
        }
        /////////////
        else if (filter == 'arrived') {
            this.setState({
                listContent: 'arrived'
            });
        }
        else if (filter == 'pickup') {
            this.setState({
                listContent: 'pickup'
            });
        }
        else if (filter == 'dropped') {
            this.setState({
                listContent: 'dropped'
            });
        }
        else if (filter == 'completed') {
            this.setState({
                listContent: 'completed'
            });
        }
        else if (filter == 'accepted') {
            this.setState({
                listContent: 'accepted'
            });
        }
        //////////
        else {
            this.setState({
                listContent: 'dispatch-map'
            });
        }
        this.props.clicked(trip);
    }

    render() {
        return (
            <div className="card">
                <div className="card-header text-uppercase"><b>Searching List</b></div>
                <DispatcherSearchListItem data={this.state.data.data} clicked={this.handleClick.bind(this)} />
            </div>
        );
    }
}

class DispatcherCancelledList extends React.Component {
    constructor(props) {
        super(props);
        this.state = {
            data: {
                data: []
            }
        };
    }

    componentDidMount() {
        window.worldMapInitialize();
        this.getTripsUpdate();
        window.Tranxit.TripTimer = setInterval(
            () => this.getTripsUpdate(),
            50000
        );
    }

    componentWillUnmount() {
        clearInterval(window.Tranxit.TripTimer);
    }

    getTripsUpdate() { //Changed API Required
        $.get('/dispatcher/dispatcher/trips?type=CANCELLED', function (result) {
            if (result.hasOwnProperty('data')) {
                this.setState({
                    data: result
                });
            } else {
                this.setState({
                    data: {
                        data: []
                    }
                });
            }
        }.bind(this));
    }

    handleClick(trip) {
        //console.log(trip);
        // if(trip.status == 'CANCELLED'){
        //     return false;
        // }
        let filter = trip.status;
        if (filter == 'all') {
            this.setState({
                listContent: 'dispatch-map'
            });
        }
        else if (filter == 'cancelled') {
            this.setState({
                listContent: 'cancelled'
            });
        }
        else if (filter == 'searching') {
            this.setState({
                listContent: 'searching'
            });
        }
        else if (filter == 'online') {
            this.setState({
                listContent: 'online'
            });
        }
        else if (filter == 'offline') {
            this.setState({
                listContent: 'offline'
            });
        }
        else if (filter == 'return') {
            this.setState({
                listContent: 'dispatch-return'
            });
        }
        /////////////
        else if (filter == 'arrived') {
            this.setState({
                listContent: 'arrived'
            });
        }
        else if (filter == 'pickup') {
            this.setState({
                listContent: 'pickup'
            });
        }
        else if (filter == 'dropped') {
            this.setState({
                listContent: 'dropped'
            });
        }
        else if (filter == 'completed') {
            this.setState({
                listContent: 'completed'
            });
        }
        else if (filter == 'accepted') {
            this.setState({
                listContent: 'accepted'
            });
        }
        //////////
        else {
            this.setState({
                listContent: 'dispatch-map'
            });
        }
        this.props.clicked(trip);
    }

    render() {
        return (
            <div className="card">
                <div className="card-header text-uppercase"><b>Cancelled List</b></div>
                <DispatcherCancelListItem data={this.state.data.data} clicked={this.handleClick.bind(this)} />
            </div>
        );
    }
}

class DispatcherCancelListItem extends React.Component {

    render() {
        var listItem = function (trip) {
            return (
                <div className="il-item" key={trip.id}>
                    <a className="text-black" href="#">
                        <div className="media">
                            <div className="media-body">
                                <p className="mb-0-5">{trip.user.first_name} {trip.user.last_name}
                                    {trip.status == 'COMPLETED' ?
                                        <span className="tag tag-success pull-right"> {trip.status} </span>
                                        : trip.status == 'CANCELLED' ?
                                            <span className="tag tag-danger pull-right"> {trip.status} </span>
                                            : trip.status == 'SEARCHING' ?
                                                <span className="tag tag-warning pull-right"> {trip.status} </span>
                                                : trip.status == 'SCHEDULES' ?
                                                    <span className="tag tag-warning pull-right"> {trip.status} </span>
                                                    : trip.status == 'SCHEDULED' ?
                                                        <span className="tag tag-primary pull-right"> {trip.status} </span>
                                                        :
                                                        <span className="tag tag-info pull-right"> {trip.status} </span>
                                    }
                                </p>
                                <h6 className="media-heading">From: {trip.s_address}</h6>
                                <h6 className="media-heading">To: {trip.d_address ? trip.d_address : "Not Selected"}</h6>
                                <div style={{display:'flex',}}>    
                                    <h6 className="media-heading">Payment: {trip.payment_mode}</h6>
                                    {trip.provider
                                        ? <h6 style={{paddingLeft: '20px'}} className="media-heading">Cab No: {trip.provider ? trip.provider.service ? trip.provider.service.service_number : '' : ''}</h6>
                                        :  ''
                                    }                                </div>
                                <progress className="progress progress-success progress-sm" max="100"></progress>
                                <span className="text-muted">Cancelled at : {trip.updated_at}</span>
                            </div>
                        </div>
                    </a>
                </div>
            );
        }.bind(this);

        return (
            <div className="items-list">
                {this.props.data.map(listItem)}
            </div>
        );
    }
}

class DispatcherSearchListItem extends React.Component {
    handleClick(trip) {
        this.props.clicked(trip)
    }
    cancelRide(trip) {
        $.get(`/dispatcher/dispatcher/cancel?request_id=${trip.id}`, function (result) {
            location.reload();
        }.bind(this));
    }
    render() {
        var listItem = function (trip) {
            return (
                <div className="il-item" key={trip.id} onClick={this.handleClick.bind(this, trip)}>
                    <p className="btn btn-danger" onClick={this.cancelRide.bind(this, trip)}>Cancel Ride</p>
                    <a className="text-black" href="#">
                        <div className="media">
                            <div className="media-body">
                                <p className="mb-0-5">{trip.user.first_name} {trip.user.last_name}
                                    {trip.status == 'COMPLETED' ?
                                        <span className="tag tag-success pull-right"> {trip.status} </span>
                                        : trip.status == 'CANCELLED' ?
                                            <span className="tag tag-danger pull-right"> {trip.status} </span>
                                            : trip.status == 'SEARCHING' ?
                                                <span className="tag tag-warning pull-right"> {trip.status} </span>
                                                : trip.status == 'SCHEDULES' ?
                                                    <span className="tag tag-warning pull-right"> {trip.status} </span>
                                                    : trip.status == 'SCHEDULED' ?
                                                        <span className="tag tag-primary pull-right"> {trip.status} </span>
                                                        :
                                                        <span className="tag tag-info pull-right"> {trip.status} </span>
                                    }
                                </p>
                                <h6 className="media-heading">From: {trip.s_address}</h6>
                                <h6 className="media-heading">To: {trip.d_address ? trip.d_address : "Not Selected"}</h6>
                                <div style={{display:'flex',}}>    
                                    <h6 className="media-heading">Payment: {trip.payment_mode}</h6>
                                    {trip.provider
                                        ? <h6 style={{paddingLeft: '20px'}} className="media-heading">Cab No: {trip.provider ? trip.provider.service ? trip.provider.service.service_number : '' : ''}</h6>
                                        :  ''
                                    }                                </div>
                                <progress className="progress progress-success progress-sm" max="100"></progress>
                                <span className="text-muted">{trip.current_provider_id == 0 ? "Manual Assignment" : "Auto Search"} : {trip.created_at}</span>
                            </div>
                        </div>
                    </a>
                </div>
            );
        }.bind(this);

        return (
            <div className="items-list">
                {this.props.data.map(listItem)}
            </div>
        );
    }
}
////////////////////
class DispatcherOfflineList extends React.Component {
    constructor(props) {
        super(props);
        this.state = {
            data: {
                data: []
            }
        };
    }

    componentDidMount() {
        window.worldMapInitialize();
        this.getTripsUpdate();
        window.Tranxit.TripTimer = setInterval(
            () => this.getTripsUpdate(),
            50000
        );
    }

    componentWillUnmount() {
        clearInterval(window.Tranxit.TripTimer);
    }

    getTripsUpdate() { //Changed API Required
        $.get('/dispatcher/dispatcher/trips?type=OFFLINE', function (result) {
            if (result.hasOwnProperty('data')) {
                this.setState({
                    data: result
                });
            } else {
                this.setState({
                    data: {
                        data: []
                    }
                });
            }
        }.bind(this));
    }

    handleClick(trip) {
        //console.log(trip);
        // if(trip.status == 'CANCELLED'){
        //     return false;
        // }
        let filter = trip.status;
        if (filter == 'all') {
            this.setState({
                listContent: 'dispatch-map'
            });
        }
        else if (filter == 'cancelled') {
            this.setState({
                listContent: 'cancelled'
            });
        }
        else if (filter == 'searching') {
            this.setState({
                listContent: 'searching'
            });
        }
        else if (filter == 'online') {
            this.setState({
                listContent: 'online'
            });
        }
        else if (filter == 'offline') {
            this.setState({
                listContent: 'offline'
            });
        }
        else if (filter == 'return') {
            this.setState({
                listContent: 'dispatch-return'
            });
        }
        /////////////
        else if (filter == 'arrived') {
            this.setState({
                listContent: 'arrived'
            });
        }
        else if (filter == 'pickup') {
            this.setState({
                listContent: 'pickup'
            });
        }
        else if (filter == 'dropped') {
            this.setState({
                listContent: 'dropped'
            });
        }
        else if (filter == 'completed') {
            this.setState({
                listContent: 'completed'
            });
        }
        else if (filter == 'accepted') {
            this.setState({
                listContent: 'accepted'
            });
        }
        //////////
        else {
            this.setState({
                listContent: 'dispatch-map'
            });
        }
        this.props.clicked(trip);
    }

    render() {
        return (
            <div className="card">
                <div className="card-header text-uppercase"><b>Offline List</b></div>
                <DispatcherOfflineListItem data={this.state.data.data} clicked={this.handleClick.bind(this)} />
            </div>
        );
    }
}

class DispatcherOfflineListItem extends React.Component {

    render() {
        var listItem = function (trip) {
            return (
                <div className="il-item" key={trip.id}>
                    <a className="text-black" href="#">
                        <div className="media">
                            <div className="media-body">
                                <p className="mb-0-5">{trip.user.first_name} {trip.user.last_name}
                                    {trip.status == 'COMPLETED' ?
                                        <span className="tag tag-success pull-right"> {trip.status} </span>
                                        : trip.status == 'CANCELLED' ?
                                            <span className="tag tag-danger pull-right"> {trip.status} </span>
                                            : trip.status == 'SEARCHING' ?
                                                <span className="tag tag-warning pull-right"> {trip.status} </span>
                                                : trip.status == 'SCHEDULES' ?
                                                    <span className="tag tag-warning pull-right"> {trip.status} </span>
                                                    : trip.status == 'SCHEDULED' ?
                                                        <span className="tag tag-primary pull-right"> {trip.status} </span>
                                                        :
                                                        <span className="tag tag-info pull-right"> {trip.status} </span>
                                    }
                                </p>
                                <h6 className="media-heading">From: {trip.s_address}</h6>
                                <h6 className="media-heading">To: {trip.d_address ? trip.d_address : "Not Selected"}</h6>
                                <div style={{display:'flex',}}>    
                                    <h6 className="media-heading">Payment: {trip.payment_mode}</h6>
                                    {trip.provider
                                        ? <h6 style={{paddingLeft: '20px'}} className="media-heading">Cab No: {trip.provider ? trip.provider.service ? trip.provider.service.service_number : '' : ''}</h6>
                                        :  ''
                                    }                                </div>
                                <progress className="progress progress-success progress-sm" max="100"></progress>
                                <span className="text-muted">Offline at : {trip.updated_at}</span>
                            </div>
                        </div>
                    </a>
                </div>
            );
        }.bind(this);

        return (
            <div className="items-list">
                {this.props.data.map(listItem)}
            </div>
        );
    }
}

class DispatcherOnlineList extends React.Component {
    constructor(props) {
        super(props);
        this.state = {
            data: {
                data: []
            }
        };
    }

    componentDidMount() {
        window.worldMapInitialize();
        this.getTripsUpdate();
        window.Tranxit.TripTimer = setInterval(
            () => this.getTripsUpdate(),
            50000
        );
    }

    componentWillUnmount() {
        clearInterval(window.Tranxit.TripTimer);
    }

    getTripsUpdate() { //Changed API Required
        $.get('/dispatcher/dispatcher/trips?type=ONLINE', function (result) {
            if (result.hasOwnProperty('data')) {
                this.setState({
                    data: result
                });
            } else {
                this.setState({
                    data: {
                        data: []
                    }
                });
            }
        }.bind(this));
    }

    handleClick(trip) {
        //console.log(trip);
        // if(trip.status == 'CANCELLED'){
        //     return false;
        // }
        let filter = trip.status;
        if (filter == 'all') {
            this.setState({
                listContent: 'dispatch-map'
            });
        }
        else if (filter == 'cancelled') {
            this.setState({
                listContent: 'cancelled'
            });
        }
        else if (filter == 'searching') {
            this.setState({
                listContent: 'searching'
            });
        }
        else if (filter == 'online') {
            this.setState({
                listContent: 'online'
            });
        }
        else if (filter == 'offline') {
            this.setState({
                listContent: 'offline'
            });
        }
        else if (filter == 'return') {
            this.setState({
                listContent: 'dispatch-return'
            });
        }
        /////////////
        else if (filter == 'arrived') {
            this.setState({
                listContent: 'arrived'
            });
        }
        else if (filter == 'pickup') {
            this.setState({
                listContent: 'pickup'
            });
        }
        else if (filter == 'dropped') {
            this.setState({
                listContent: 'dropped'
            });
        }
        else if (filter == 'completed') {
            this.setState({
                listContent: 'completed'
            });
        }
        else if (filter == 'accepted') {
            this.setState({
                listContent: 'accepted'
            });
        }
        //////////
        else {
            this.setState({
                listContent: 'dispatch-map'
            });
        }
        this.props.clicked(trip);
    }

    render() {
        return (
            <div className="card">
                <div className="card-header text-uppercase"><b>Online List</b></div>
                <DispatcherOnlineListItem data={this.state.data.data} clicked={this.handleClick.bind(this)} />
            </div>
        );
    }
}

class DispatcherOnlineListItem extends React.Component {

    render() {
        var listItem = function (trip) {
            return (
                <div className="il-item" key={trip.id}>
                    <a className="text-black" href="#">
                        <div className="media">
                            <div className="media-body">
                                <p className="mb-0-5">{trip.user.first_name} {trip.user.last_name}
                                    {trip.status == 'COMPLETED' ?
                                        <span className="tag tag-success pull-right"> {trip.status} </span>
                                        : trip.status == 'CANCELLED' ?
                                            <span className="tag tag-danger pull-right"> {trip.status} </span>
                                            : trip.status == 'SEARCHING' ?
                                                <span className="tag tag-warning pull-right"> {trip.status} </span>
                                                : trip.status == 'SCHEDULES' ?
                                                    <span className="tag tag-warning pull-right"> {trip.status} </span>
                                                    : trip.status == 'SCHEDULED' ?
                                                        <span className="tag tag-primary pull-right"> {trip.status} </span>
                                                        :
                                                        <span className="tag tag-info pull-right"> {trip.status} </span>
                                    }
                                </p>
                                <h6 className="media-heading">From: {trip.s_address}</h6>
                                <h6 className="media-heading">To: {trip.d_address ? trip.d_address : "Not Selected"}</h6>
                                <div style={{display:'flex',}}>    
                                    <h6 className="media-heading">Payment: {trip.payment_mode}</h6>
                                    {trip.provider
                                        ? <h6 style={{paddingLeft: '20px'}} className="media-heading">Cab No: {trip.provider ? trip.provider.service ? trip.provider.service.service_number : '' : ''}</h6>
                                        :  ''
                                    }                                </div>
                                <progress className="progress progress-success progress-sm" max="100"></progress>
                                <span className="text-muted">Online at : {trip.updated_at}</span>
                            </div>
                        </div>
                    </a>
                </div>
            );
        }.bind(this);

        return (
            <div className="items-list">
                {this.props.data.map(listItem)}
            </div>
        );
    }
}

class DispatcherPickupList extends React.Component {
    constructor(props) {
        super(props);
        this.state = {
            data: {
                data: []
            }
        };
    }

    componentDidMount() {
        window.worldMapInitialize();
        this.getTripsUpdate();
        window.Tranxit.TripTimer = setInterval(
            () => this.getTripsUpdate(),
            50000
        );
    }

    componentWillUnmount() {
        clearInterval(window.Tranxit.TripTimer);
    }

    getTripsUpdate() { //Changed API Required
        $.get('/dispatcher/dispatcher/trips?type=PICKEDUP', function (result) {
            if (result.hasOwnProperty('data')) {
                this.setState({
                    data: result
                });
            } else {
                this.setState({
                    data: {
                        data: []
                    }
                });
            }
        }.bind(this));
    }

    handleClick(trip) {
        //console.log(trip);
        // if(trip.status == 'CANCELLED'){
        //     return false;
        // }
        let filter = trip.status;
        if (filter == 'all') {
            this.setState({
                listContent: 'dispatch-map'
            });
        }
        else if (filter == 'cancelled') {
            this.setState({
                listContent: 'cancelled'
            });
        }
        else if (filter == 'searching') {
            this.setState({
                listContent: 'searching'
            });
        }
        else if (filter == 'online') {
            this.setState({
                listContent: 'online'
            });
        }
        else if (filter == 'offline') {
            this.setState({
                listContent: 'offline'
            });
        }
        else if (filter == 'return') {
            this.setState({
                listContent: 'dispatch-return'
            });
        }
        /////////////
        else if (filter == 'arrived') {
            this.setState({
                listContent: 'arrived'
            });
        }
        else if (filter == 'pickup') {
            this.setState({
                listContent: 'pickup'
            });
        }
        else if (filter == 'dropped') {
            this.setState({
                listContent: 'dropped'
            });
        }
        else if (filter == 'completed') {
            this.setState({
                listContent: 'completed'
            });
        }
        else if (filter == 'accepted') {
            this.setState({
                listContent: 'accepted'
            });
        }
        //////////
        else {
            this.setState({
                listContent: 'dispatch-map'
            });
        }
        this.props.clicked(trip);
    }
    render() {
        return (
            <div className="card">
                <div className="card-header text-uppercase"><b>Pickup List</b></div>
                <DispatcherPickupListItem data={this.state.data.data} clicked={this.handleClick.bind(this)} />
            </div>
        );
    }
}

class DispatcherPickupListItem extends React.Component {

    render() {
        var listItem = function (trip) {
            return (
                <div className="il-item" key={trip.id}>
                    <a className="text-black" href="#">
                        <div className="media">
                            <div className="media-body">
                                <p className="mb-0-5">{trip.user.first_name} {trip.user.last_name}
                                    {trip.status == 'COMPLETED' ?
                                        <span className="tag tag-success pull-right"> {trip.status} </span>
                                        : trip.status == 'CANCELLED' ?
                                            <span className="tag tag-danger pull-right"> {trip.status} </span>
                                            : trip.status == 'SEARCHING' ?
                                                <span className="tag tag-warning pull-right"> {trip.status} </span>
                                                : trip.status == 'SCHEDULES' ?
                                                    <span className="tag tag-warning pull-right"> {trip.status} </span>
                                                    : trip.status == 'SCHEDULED' ?
                                                        <span className="tag tag-primary pull-right"> {trip.status} </span>
                                                        :
                                                        <span className="tag tag-info pull-right"> {trip.status} </span>
                                    }
                                </p>
                                <h6 className="media-heading">From: {trip.s_address}</h6>
                                <h6 className="media-heading">To: {trip.d_address ? trip.d_address : "Not Selected"}</h6>
                                <div style={{display:'flex',}}>    
                                    <h6 className="media-heading">Payment: {trip.payment_mode}</h6>
                                    {trip.provider
                                        ? <h6 style={{paddingLeft: '20px'}} className="media-heading">Cab No: {trip.provider ? trip.provider.service ? trip.provider.service.service_number : '' : ''}</h6>
                                        :  ''
                                    }                                </div>
                                <progress className="progress progress-success progress-sm" max="100"></progress>
                                <span className="text-muted">Pickup at : {trip.updated_at}</span>
                            </div>
                        </div>
                    </a>
                </div>
            );
        }.bind(this);

        return (
            <div className="items-list">
                {this.props.data.map(listItem)}
            </div>
        );
    }
}

class DispatcherArrivedList extends React.Component {
    constructor(props) {
        super(props);
        this.state = {
            data: {
                data: []
            }
        };
    }

    componentDidMount() {
        window.worldMapInitialize();
        this.getTripsUpdate();
        window.Tranxit.TripTimer = setInterval(
            () => this.getTripsUpdate(),
            50000
        );
    }

    componentWillUnmount() {
        clearInterval(window.Tranxit.TripTimer);
    }

    getTripsUpdate() { //Changed API Required
        $.get('/dispatcher/dispatcher/trips?type=ARRIVED', function (result) {
            if (result.hasOwnProperty('data')) {
                this.setState({
                    data: result
                });
            } else {
                this.setState({
                    data: {
                        data: []
                    }
                });
            }
        }.bind(this));
    }

    handleClick(trip) {
        //console.log(trip);
        // if(trip.status == 'CANCELLED'){
        //     return false;
        // }
        let filter = trip.status;
        if (filter == 'all') {
            this.setState({
                listContent: 'dispatch-map'
            });
        }
        else if (filter == 'cancelled') {
            this.setState({
                listContent: 'cancelled'
            });
        }
        else if (filter == 'searching') {
            this.setState({
                listContent: 'searching'
            });
        }
        else if (filter == 'online') {
            this.setState({
                listContent: 'online'
            });
        }
        else if (filter == 'offline') {
            this.setState({
                listContent: 'offline'
            });
        }
        else if (filter == 'return') {
            this.setState({
                listContent: 'dispatch-return'
            });
        }
        /////////////
        else if (filter == 'arrived') {
            this.setState({
                listContent: 'arrived'
            });
        }
        else if (filter == 'pickup') {
            this.setState({
                listContent: 'pickup'
            });
        }
        else if (filter == 'dropped') {
            this.setState({
                listContent: 'dropped'
            });
        }
        else if (filter == 'completed') {
            this.setState({
                listContent: 'completed'
            });
        }
        else if (filter == 'accepted') {
            this.setState({
                listContent: 'accepted'
            });
        }
        //////////
        else {
            this.setState({
                listContent: 'dispatch-map'
            });
        }
        this.props.clicked(trip);
    }

    render() {
        return (
            <div className="card">
                <div className="card-header text-uppercase"><b>Arrived List</b></div>
                <DispatcherArrivedListItem data={this.state.data.data} clicked={this.handleClick.bind(this)} />
            </div>
        );
    }
}

class DispatcherArrivedListItem extends React.Component {

    render() {
        var listItem = function (trip) {
            return (
                <div className="il-item" key={trip.id}>
                    <a className="text-black" href="#">
                        <div className="media">
                            <div className="media-body">
                                <p className="mb-0-5">{trip.user.first_name} {trip.user.last_name}
                                    {trip.status == 'COMPLETED' ?
                                        <span className="tag tag-success pull-right"> {trip.status} </span>
                                        : trip.status == 'CANCELLED' ?
                                            <span className="tag tag-danger pull-right"> {trip.status} </span>
                                            : trip.status == 'SEARCHING' ?
                                                <span className="tag tag-warning pull-right"> {trip.status} </span>
                                                : trip.status == 'OFFLINE' ?
                                                    <span className="tag tag-warning pull-right"> {trip.status} </span>
                                                    : trip.status == 'ONLINE' ?
                                                        <span className="tag tag-warning pull-right"> {trip.status} </span>
                                                        : trip.status == 'PICKUP' ?
                                                            <span className="tag tag-warning pull-right"> {trip.status} </span>
                                                            : trip.status == 'ARRIVED' ?
                                                                <span className="tag tag-warning pull-right"> {trip.status} </span>
                                                                : trip.status == 'SCHEDULES' ?
                                                                    <span className="tag tag-warning pull-right"> {trip.status} </span>
                                                                    : trip.status == 'SCHEDULED' ?
                                                                        <span className="tag tag-primary pull-right"> {trip.status} </span>
                                                                        :
                                                                        <span className="tag tag-info pull-right"> {trip.status} </span>
                                    }
                                </p>
                                <h6 className="media-heading">From: {trip.s_address}</h6>
                                <h6 className="media-heading">To: {trip.d_address ? trip.d_address : "Not Selected"}</h6>
                                <div style={{display:'flex',}}>    
                                    <h6 className="media-heading">Payment: {trip.payment_mode}</h6>
                                    {trip.provider
                                        ? <h6 style={{paddingLeft: '20px'}} className="media-heading">Cab No: {trip.provider ? trip.provider.service ? trip.provider.service.service_number : '' : ''}</h6>
                                        :  ''
                                    }                                </div>
                                <progress className="progress progress-success progress-sm" max="100"></progress>
                                <span className="text-muted">Arrived at : {trip.updated_at}</span>
                            </div>
                        </div>
                    </a>
                </div>
            );
        }.bind(this);

        return (
            <div className="items-list">
                {this.props.data.map(listItem)}
            </div>
        );
    }
}

//////////////////////
class DispatcherAcceptedList extends React.Component {
    constructor(props) {
        super(props);
        this.state = {
            data: {
                data: []
            }
        };
    }

    componentDidMount() {
        window.worldMapInitialize();
        this.getTripsUpdate();
        window.Tranxit.TripTimer = setInterval(
            () => this.getTripsUpdate(),
            50000
        );
    }

    componentWillUnmount() {
        clearInterval(window.Tranxit.TripTimer);
    }

    getTripsUpdate() { //Changed API Required
        $.get('/dispatcher/dispatcher/trips?type=STARTED', function (result) {
            if (result.hasOwnProperty('data')) {
                this.setState({
                    data: result
                });
            } else {
                this.setState({
                    data: {
                        data: []
                    }
                });
            }
        }.bind(this));
    }

    handleClick(trip) {
        //console.log(trip);
        // if(trip.status == 'CANCELLED'){
        //     return false;
        // }
        let filter = trip.status;
        if (filter == 'all') {
            this.setState({
                listContent: 'dispatch-map'
            });
        }
        else if (filter == 'cancelled') {
            this.setState({
                listContent: 'cancelled'
            });
        }
        else if (filter == 'searching') {
            this.setState({
                listContent: 'searching'
            });
        }
        else if (filter == 'online') {
            this.setState({
                listContent: 'online'
            });
        }
        else if (filter == 'offline') {
            this.setState({
                listContent: 'offline'
            });
        }
        else if (filter == 'return') {
            this.setState({
                listContent: 'dispatch-return'
            });
        }
        /////////////
        else if (filter == 'arrived') {
            this.setState({
                listContent: 'arrived'
            });
        }
        else if (filter == 'pickup') {
            this.setState({
                listContent: 'pickup'
            });
        }
        else if (filter == 'dropped') {
            this.setState({
                listContent: 'dropped'
            });
        }
        else if (filter == 'completed') {
            this.setState({
                listContent: 'completed'
            });
        }
        else if (filter == 'accepted') {
            this.setState({
                listContent: 'accepted'
            });
        }
        //////////
        else {
            this.setState({
                listContent: 'dispatch-map'
            });
        }
        this.props.clicked(trip);
    }

    render() {
        return (
            <div className="card">
                <div className="card-header text-uppercase"><b>Arrived List</b></div>
                <DispatcherAcceptedListItem data={this.state.data.data} clicked={this.handleClick.bind(this)} />
            </div>
        );
    }
}

class DispatcherAcceptedListItem extends React.Component {

    render() {
        var listItem = function (trip) {
            return (
                <div className="il-item" key={trip.id}>
                    <a className="text-black" href="#">
                        <div className="media">
                            <div className="media-body">
                                <p className="mb-0-5">{trip.user.first_name} {trip.user.last_name}
                                    {trip.status == 'COMPLETED' ?
                                        <span className="tag tag-success pull-right"> {trip.status} </span>
                                        : trip.status == 'CANCELLED' ?
                                            <span className="tag tag-danger pull-right"> {trip.status} </span>
                                            : trip.status == 'SEARCHING' ?
                                                <span className="tag tag-warning pull-right"> {trip.status} </span>
                                                : trip.status == 'OFFLINE' ?
                                                    <span className="tag tag-warning pull-right"> {trip.status} </span>
                                                    : trip.status == 'ONLINE' ?
                                                        <span className="tag tag-warning pull-right"> {trip.status} </span>
                                                        : trip.status == 'ACCEPTED' ?
                                                            <span className="tag tag-warning pull-right"> {trip.status} </span>
                                                            : trip.status == 'PICKUP' ?
                                                                <span className="tag tag-warning pull-right"> {trip.status} </span>
                                                                : trip.status == 'ARRIVED' ?
                                                                    <span className="tag tag-warning pull-right"> {trip.status} </span>
                                                                    : trip.status == 'SCHEDULES' ?
                                                                        <span className="tag tag-warning pull-right"> {trip.status} </span>
                                                                        : trip.status == 'SCHEDULED' ?
                                                                            <span className="tag tag-primary pull-right"> {trip.status} </span>
                                                                            :
                                                                            <span className="tag tag-info pull-right"> {trip.status} </span>
                                    }
                                </p>
                                <h6 className="media-heading">From: {trip.s_address}</h6>
                                <h6 className="media-heading">To: {trip.d_address ? trip.d_address : "Not Selected"}</h6>
                                <div style={{display:'flex',}}>    
                                    <h6 className="media-heading">Payment: {trip.payment_mode}</h6>
                                    {trip.provider
                                        ? <h6 style={{paddingLeft: '20px'}} className="media-heading">Cab No: {trip.provider ? trip.provider.service ? trip.provider.service.service_number : '' : ''}</h6>
                                        :  ''
                                    }                                </div>
                                <progress className="progress progress-success progress-sm" max="100"></progress>
                                <span className="text-muted">Accepted at : {trip.updated_at}</span>
                            </div>
                        </div>
                    </a>
                </div>
            );
        }.bind(this);

        return (
            <div className="items-list">
                {this.props.data.map(listItem)}
            </div>
        );
    }
}
///////////////////
class DispatcherCompletedList extends React.Component {
    constructor(props) {
        super(props);
        this.state = {
            data: {
                data: []
            }
        };
    }

    componentDidMount() {
        window.worldMapInitialize();
        this.getTripsUpdate();
        window.Tranxit.TripTimer = setInterval(
            () => this.getTripsUpdate(),
            50000
        );
    }

    componentWillUnmount() {
        clearInterval(window.Tranxit.TripTimer);
    }

    getTripsUpdate() { //Changed API Required
        $.get('/dispatcher/dispatcher/trips?type=COMPLETED', function (result) {
            if (result.hasOwnProperty('data')) {
                this.setState({
                    data: result
                });
            } else {
                this.setState({
                    data: {
                        data: []
                    }
                });
            }
        }.bind(this));
    }

    handleClick(trip) {
        //console.log(trip);
        // if(trip.status == 'CANCELLED'){
        //     return false;
        // }
        let filter = trip.status;
        if (filter == 'all') {
            this.setState({
                listContent: 'dispatch-map'
            });
        }
        else if (filter == 'cancelled') {
            this.setState({
                listContent: 'cancelled'
            });
        }
        else if (filter == 'searching') {
            this.setState({
                listContent: 'searching'
            });
        }
        else if (filter == 'online') {
            this.setState({
                listContent: 'online'
            });
        }
        else if (filter == 'offline') {
            this.setState({
                listContent: 'offline'
            });
        }
        else if (filter == 'return') {
            this.setState({
                listContent: 'dispatch-return'
            });
        }
        /////////////
        else if (filter == 'arrived') {
            this.setState({
                listContent: 'arrived'
            });
        }
        else if (filter == 'pickup') {
            this.setState({
                listContent: 'pickup'
            });
        }
        else if (filter == 'dropped') {
            this.setState({
                listContent: 'dropped'
            });
        }
        else if (filter == 'completed') {
            this.setState({
                listContent: 'completed'
            });
        }
        else if (filter == 'accepted') {
            this.setState({
                listContent: 'accepted'
            });
        }
        //////////
        else {
            this.setState({
                listContent: 'dispatch-map'
            });
        }
        this.props.clicked(trip);
    }

    render() {
        return (
            <div className="card">
                <div className="card-header text-uppercase"><b>Arrived List</b></div>
                <DispatcherCompletedListItem data={this.state.data.data} clicked={this.handleClick.bind(this)} />
            </div>
        );
    }
}

class DispatcherCompletedListItem extends React.Component {

    render() {
        var listItem = function (trip) {
            return (
                <div className="il-item" key={trip.id}>
                    <a className="text-black" href="#">
                        <div className="media">
                            <div className="media-body">
                                <p className="mb-0-5">{trip.user.first_name} {trip.user.last_name}
                                    {trip.status == 'COMPLETED' ?
                                        <span className="tag tag-success pull-right"> {trip.status} </span>
                                        : trip.status == 'CANCELLED' ?
                                            <span className="tag tag-danger pull-right"> {trip.status} </span>
                                            : trip.status == 'SEARCHING' ?
                                                <span className="tag tag-warning pull-right"> {trip.status} </span>
                                                : trip.status == 'OFFLINE' ?
                                                    <span className="tag tag-warning pull-right"> {trip.status} </span>
                                                    : trip.status == 'ONLINE' ?
                                                        <span className="tag tag-warning pull-right"> {trip.status} </span>
                                                        : trip.status == 'PICKUP' ?
                                                            <span className="tag tag-warning pull-right"> {trip.status} </span>
                                                            : trip.status == 'ARRIVED' ?
                                                                <span className="tag tag-warning pull-right"> {trip.status} </span>
                                                                : trip.status == 'SCHEDULES' ?
                                                                    <span className="tag tag-warning pull-right"> {trip.status} </span>
                                                                    : trip.status == 'SCHEDULED' ?
                                                                        <span className="tag tag-primary pull-right"> {trip.status} </span>
                                                                        :
                                                                        <span className="tag tag-info pull-right"> {trip.status} </span>
                                    }
                                </p>
                                <h6 className="media-heading">From: {trip.s_address}</h6>
                                <h6 className="media-heading">To: {trip.d_address ? trip.d_address : "Not Selected"}</h6>
                                <div style={{display:'flex',}}>    
                                    <h6 className="media-heading">Payment: {trip.payment_mode}</h6>
                                    {trip.provider
                                        ? <h6 style={{paddingLeft: '20px'}} className="media-heading">Cab No: {trip.provider ? trip.provider.service ? trip.provider.service.service_number : '' : ''}</h6>
                                        :  ''
                                    }                                </div>
                                <progress className="progress progress-success progress-sm" max="100"></progress>
                                <span className="text-muted">Completed at : {trip.updated_at}</span>
                            </div>
                        </div>
                    </a>
                </div>
            );
        }.bind(this);

        return (
            <div className="items-list">
                {this.props.data.map(listItem)}
            </div>
        );
    }
}
/////////////////////
class DispatcherDroppedList extends React.Component {
    constructor(props) {
        super(props);
        this.state = {
            data: {
                data: []
            }
        };
    }

    componentDidMount() {
        window.worldMapInitialize();
        this.getTripsUpdate();
        window.Tranxit.TripTimer = setInterval(
            () => this.getTripsUpdate(),
            50000
        );
    }

    componentWillUnmount() {
        clearInterval(window.Tranxit.TripTimer);
    }

    getTripsUpdate() { //Changed API Required
        $.get('/dispatcher/dispatcher/trips?type=DROPPED', function (result) {
            if (result.hasOwnProperty('data')) {
                this.setState({
                    data: result
                });
            } else {
                this.setState({
                    data: {
                        data: []
                    }
                });
            }
        }.bind(this));
    }
    handleClick(trip) {
        //console.log(trip);
        // if(trip.status == 'CANCELLED'){
        //     return false;
        // }
        let filter = trip.status;
        if (filter == 'all') {
            this.setState({
                listContent: 'dispatch-map'
            });
        }
        else if (filter == 'cancelled') {
            this.setState({
                listContent: 'cancelled'
            });
        }
        else if (filter == 'searching') {
            this.setState({
                listContent: 'searching'
            });
        }
        else if (filter == 'online') {
            this.setState({
                listContent: 'online'
            });
        }
        else if (filter == 'offline') {
            this.setState({
                listContent: 'offline'
            });
        }
        else if (filter == 'return') {
            this.setState({
                listContent: 'dispatch-return'
            });
        }
        /////////////
        else if (filter == 'arrived') {
            this.setState({
                listContent: 'arrived'
            });
        }
        else if (filter == 'pickup') {
            this.setState({
                listContent: 'pickup'
            });
        }
        else if (filter == 'dropped') {
            this.setState({
                listContent: 'dropped'
            });
        }
        else if (filter == 'completed') {
            this.setState({
                listContent: 'completed'
            });
        }
        else if (filter == 'accepted') {
            this.setState({
                listContent: 'accepted'
            });
        }
        //////////
        else {
            this.setState({
                listContent: 'dispatch-map'
            });
        }
        this.props.clicked(trip);
    }

    render() {
        return (
            <div className="card">
                <div className="card-header text-uppercase"><b>Arrived List</b></div>
                <DispatcherDroppedListItem data={this.state.data.data} clicked={this.handleClick.bind(this)} />
            </div>
        );
    }
}

class DispatcherDroppedListItem extends React.Component {

    render() {
        var listItem = function (trip) {
            return (
                <div className="il-item" key={trip.id}>
                    <a className="text-black" href="#">
                        <div className="media">
                            <div className="media-body">
                                <p className="mb-0-5">{trip.user.first_name} {trip.user.last_name}
                                    {trip.status == 'COMPLETED' ?
                                        <span className="tag tag-success pull-right"> {trip.status} </span>
                                        : trip.status == 'CANCELLED' ?
                                            <span className="tag tag-danger pull-right"> {trip.status} </span>
                                            : trip.status == 'SEARCHING' ?
                                                <span className="tag tag-warning pull-right"> {trip.status} </span>
                                                : trip.status == 'OFFLINE' ?
                                                    <span className="tag tag-warning pull-right"> {trip.status} </span>
                                                    : trip.status == 'ONLINE' ?
                                                        <span className="tag tag-warning pull-right"> {trip.status} </span>
                                                        : trip.status == 'DROPPED' ?
                                                            <span className="tag tag-warning pull-right"> {trip.status} </span>
                                                            : trip.status == 'PICKUP' ?
                                                                <span className="tag tag-warning pull-right"> {trip.status} </span>
                                                                : trip.status == 'ARRIVED' ?
                                                                    <span className="tag tag-warning pull-right"> {trip.status} </span>
                                                                    : trip.status == 'SCHEDULES' ?
                                                                        <span className="tag tag-warning pull-right"> {trip.status} </span>
                                                                        : trip.status == 'SCHEDULED' ?
                                                                            <span className="tag tag-primary pull-right"> {trip.status} </span>
                                                                            :
                                                                            <span className="tag tag-info pull-right"> {trip.status} </span>
                                    }
                                </p>
                                <h6 className="media-heading">From: {trip.s_address}</h6>
                                <h6 className="media-heading">To: {trip.d_address ? trip.d_address : "Not Selected"}</h6>
                                <div style={{display:'flex',}}>    
                                    <h6 className="media-heading">Payment: {trip.payment_mode}</h6>
                                    {trip.provider
                                        ? <h6 style={{paddingLeft: '20px'}} className="media-heading">Cab No: {trip.provider ? trip.provider.service ? trip.provider.service.service_number : '' : ''}</h6>
                                        :  ''
                                    }                                </div>
                                <progress className="progress progress-success progress-sm" max="100"></progress>
                                <span className="text-muted">Dropped at : {trip.updated_at}</span>
                            </div>
                        </div>
                    </a>
                </div>
            );
        }.bind(this);

        return (
            <div className="items-list">
                {this.props.data.map(listItem)}
            </div>
        );
    }
}
//////////////
ReactDOM.render(
    <DispatcherPanel />,
    document.getElementById('dispatcher-panel')
);
