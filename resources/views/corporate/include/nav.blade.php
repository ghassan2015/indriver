<div class="site-sidebar">
	<div class="custom-scroll custom-scroll-light">
		<ul class="sidebar-menu">
			<li class="menu-title">Corporate Dashboard</li>
			<li>
				<a href="{{ route('corporate.dashboard') }}" class="waves-effect waves-light">
					<span class="s-icon"><i class="ti-anchor"></i></span>
					<span class="s-text">Dashboard</span>
				</a>
			</li>
			
			<li class="menu-title">Members</li>
			<li class="with-sub">
				<a href="#" class="waves-effect  waves-light">
					<span class="s-caret"><i class="fa fa-angle-down"></i></span>
					<span class="s-icon"><i class="ti-car"></i></span>
					<span class="s-text">Users</span>
				</a>
				<ul>
					<li><a href="{{route('corporate.user.index')}}">List Users</a></li>
					<li><a href="{{route('corporate.user.create')}}">Add New User</a></li>
				</ul>
			</li>  
			<li class="menu-title">Requests</li>
			<li>
				<a href="{{route('corporate.requests.index')}}" class="waves-effect  waves-light">
					<span class="s-icon"><i class="ti-infinite"></i></span>
					<span class="s-text">Request History</span>
				</a>
			</li> 

			<li class="menu-title">Wallet</li>
			<li>
				<a href="{{route('corporate.wallet.recharge')}}" class="waves-effect  waves-light">
					<span class="s-icon"><i class="fa fa-money"></i></span>
					<span class="s-text">Recharge & Transaction</span>
				</a>
			</li> 
			
			<li class="menu-title">Account</li>
			<li>
				<a href="{{ route('corporate.profile') }}" class="waves-effect  waves-light">
					<span class="s-icon"><i class="ti-user"></i></span>
					<span class="s-text">Account Settings</span>
				</a>
			</li>
			<li>
				<a href="{{ route('corporate.password') }}" class="waves-effect  waves-light">
					<span class="s-icon"><i class="ti-exchange-vertical"></i></span>
					<span class="s-text">Change Password</span>
				</a>
			</li>
			<li class="compact-hide">
				<a href="{{ url('/corporate/logout') }}"
                            onclick="event.preventDefault();
                                     document.getElementById('logout-form').submit();">
					<span class="s-icon"><i class="ti-power-off"></i></span>
					<span class="s-text">Logout</span>
                </a>

                <form id="logout-form" action="{{ url('/corporate/logout') }}" method="POST" style="display: none;">
                    {{ csrf_field() }}
                </form>
			</li>
			
		</ul>
	</div>
</div>