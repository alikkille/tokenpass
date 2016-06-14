@extends('accounts.base')

@section('body_class') dashboard pockets @endsection

@section('accounts_content')

<div id="pocketsController">

  <div id="verifyPocketModal" class="modal-container">
    <div class="modal-bg"></div>
    <div class="modal-content">
      <h3 class="light">Verify Pocket</h3>
      <p>
        In order for Tokenpass to track your address balances
        and provide access to "Token Controlled Access" features, you must 
        first prove ownership of this bitcoin address. 
      </p>
      <p>
        To verify address ownership, open up your Counterparty compatible Bitcoin wallet
        and use the <strong>Sign Message</strong> feature.
      </p>
      <p>
        Sign the verification code below and submit the resulting
        signature. 
      </p>

      <form action="/inventory/address/@{{ currentPocket.address }}/verify" method="post">
        
          <label for="verify-code">Verification Code</label>
          
          <input type="text" id="verify-code" value="code-@{{ currentPocket.address }}" disabled>

          <label for="verify-address">BTC Address:</label>
          <input type="text" id="verify-sig" readonly value="@{{ currentPocket.address }}" />

          <label for="verify-sig">Enter Message Signature:</label>
          <textarea name="sig" id="verify-sig" rows="8" required></textarea>

        <button type="submit">Verify</button>
      </form>
    </div>
  </div> <!-- End Verify Modal  -->

  <div id="addPocketModal" class="modal-container">
    <div class="modal-bg"></div>
    <div class="modal-content">
      <h3 class="light">Register New Address</h3>
      <div class="modal-x close-modal">
        <i class="material-icons">cancel</i>
      </div>
      <form action="/inventory/address/new" method="post">
        <label for="address">BTC Address *</label>
        <input type="text" name="address" placeholder="1F1tAaz5x1HUXrCNLbtMDqcw6o5GNn4xqX" required>

        <label for="label">Reference Label</label>
        <input type="text" name="label" placeholder="Tokenly Wallet">

        <div class="input-group">
          <label>Make this pocket public? *</label>
          <input id="pocket-new-public" class="toggle toggle-round-flat" type="checkbox" name="public" checked>
          <label for="pocket-new-public"></label>
        </div>
        <button type="submit">Submit</button>
      </form>

      <div class="or-divider-module">
        <div class="divider">.</div>
        <div class="or">or</div>
        <div class="divider">.</div>
      </div>

      <h3 class="light">Register With Mobile</h3>
      <p>
        If you are using a bitcoin wallet on your mobile device (e.g <a href="https://wallet.indiesquare.me/" target="_blank">IndieSquareWallet</a>) which
        supports <strong>Tokenpass Instant Address Verification</strong>, you may scan the 
        <strong>QR code</strong> below to register and 
        verify ownership of your bitcoin address in a single step.
      </p>
      <p class="text-center">
        <?php
        $verify_message = \TKAccounts\Models\Address::getUserVerificationCode($user);
        ?>
        <span title="Scan with your mobile device" id="instant-address-qr" data-verify-message="{{ $verify_message['user_meta'] }}">
          <?php echo QrCode::size(200)->generate(route('api.instant-verify', $user->username).'?msg='.$verify_message['user_meta']) ?>
        </span>
      </p>
    </div>
  </div> <!-- End Add Pocket Modal  -->

  <section class="title">
    <span class="heading">Pockets</span>
    <div class="search-wrapper">
      <input type="text" placeholder="Search for a pocket..." v-model="search">
      <div class="icon"><i class="material-icons">search</i></div>
    </div>
    <button data-modal="addPocketModal" class="btn-dash-title add-pocket-btn reveal-modal">+ Add Pocket</button>
  </section>

  <section id="pocketsList" class="pockets">
    <div class="pocket" v-for="pocket in pockets | filterBy search">
      <div class="pocket-main">
        <div class="pocket-indicator">
          <i class="material-icons">check</i>
        </div>
        <div class="primary-info">
          <span class="name">
            @{{ pocket.label || 'n/a' }}
          </span>
          <span class="address">@{{ pocket.address }}</span>
        </div>
        <div v-on:click="toggleEdit" class="settings-btn">  
          <i class="material-icons">settings</i>
        </div>
        <div data-modal="verifyPocketModal" v-on:click="setCurrentPocket(pocket)" v-show="!pocket.verified" class="verify-btn reveal-modal">
          Verify
        </div>
        <div class="clear"></div>
      </div><!-- End Pocket Information -->
      <div class="pocket-settings">
        <form action="">
          <label for="">Label</label>
          <input type="text" value="@{{ pocket.label }}" placeholder="Tokenly Wallet">

          <label for="">Address</label>
          <input type="text" value="@{{ pocket.address }}" disabled>
          
          <label for="">Notes</label>
          <textarea placeholder="Use this field for personal notes about this pocket. This will not affect the pocket in any way."></textarea>
          <div class="toggles-container">
            <div class="input-group toggle-field">
              <label>Active?</label>
              <input id="pocket-@{{ $index }}-active" name="active" type="checkbox" class="toggle toggle-round-flat" v-model="pocket.active_toggle">
              <label for="pocket-@{{ $index }}-active"></label>
            </div>

            <div class="input-group toggle-field">
              <label>Public?</label>
              <input id="pocket-@{{ $index }}-public" name="public" type="checkbox" class="toggle toggle-round-flat" v-model="pocket.public">
              <label for="pocket-@{{ $index }}-public"></label>
            </div>

            <div class="input-group toggle-field">
              <label>Enable for login?</label>
              <input id="pocket-@{{ $index }}-login" name="login" type="checkbox" class="toggle toggle-round-flat">
              <label for="pocket-@{{ $index }}-login"></label>
            </div>
          </div>
          <button type="submit" class="btn-save">Save</button>
          <button type="submit" class="btn-delete">Delete</button>
        </form>
      </div> <!-- End Pocket Settings -->
    </div> <!-- End Pocket List -->
  </section>

</div>

@endsection

@section('page-js')
<script>

var pockets = JSON.parse('{!! json_encode($addresses) !!}');

var vm = new Vue({
  el: '#pocketsController',
  data: {
    search: '',
    pockets: pockets,
    currentPocket: {}
  },
  methods: {
    to_bool: function(n) {
      return n == 1;
    },
    toggleEdit: function(event){
      var $pocket = $(event.target).closest('.pocket');
      var $settingsButton = $pocket.find('.settings-btn i');
      if ($pocket.hasClass('editing')) {
        $pocket.removeClass('editing');
        $settingsButton.text('settings')
      } else {
        $pocket.addClass('editing');
        $settingsButton.text('cancel')
      }
    },
    setCurrentPocket: function(pocket){
      this.currentPocket = pocket;
    },
    getVerificationCode: function(pocket){
      // $.ajax('/getPocketVerificationCode' , {
      //   success: function(data) {
      //     pocket.verificationCode = data.verificationCode;
      //   }
      // })
    }
  }
});

// Initialize new address modal
var addAddressModal = new Modal();
addAddressModal.init(document.getElementById(
  'addPocketModal'));

// Initialize verify address modal
var verifyAddressModal = new Modal();
verifyAddressModal.init(document.getElementById(
  'verifyPocketModal'));
</script>

@endsection