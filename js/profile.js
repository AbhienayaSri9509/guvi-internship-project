// js/profile.js
$(function() {
  const token = localStorage.getItem('sessionToken');
  if (!token) {
    // no token -> go to login
    window.location.href = 'login.html';
    return;
  }

  // load profile
  function loadProfile() {
    $.ajax({
      url: 'php/get_profile.php',
      method: 'GET',
      headers: { 'X-Session-Token': token },
      success: function(res) {
        if (res.success && res.user) {
          $('#profileName').text(res.user.name || 'Your Profile');
          $('#name').val(res.user.name || '');
          $('#age').val(res.user.age ?? '');
          $('#dob').val(res.user.dob ?? '');
          $('#contact').val(res.user.contact ?? '');
        } else {
          // token invalid or expired
          localStorage.removeItem('sessionToken');
          window.location.href = 'login.html';
        }
      },
      error: function() {
        $('#msg').html('<div class="alert alert-danger">Failed to load profile.</div>');
      }
    });
  }

  loadProfile();

  $('#saveBtn').click(function(e) {
    e.preventDefault();
    const age = $('#age').val();
    const dob = $('#dob').val();
    const contact = $('#contact').val()?.trim();

    if (contact && !/^\d{10}$/.test(contact)) {
      $('#msg').html('<div class="alert alert-danger">Contact must be 10 digits.</div>');
      return;
    }

    const payload = { age: age ? parseInt(age) : null, dob: dob || null, contact: contact || null };

    $.ajax({
      url: 'php/update_profile.php',
      method: 'POST',
      headers: { 'X-Session-Token': token },
      contentType: 'application/json',
      data: JSON.stringify(payload),
      success: function(res) {
        if (res.success) {
          $('#msg').html('<div class="alert alert-success">' + res.message + '</div>');
          // reload profile to get normalized data
          setTimeout(loadProfile, 600);
        } else {
          $('#msg').html('<div class="alert alert-danger">' + (res.message || 'Update failed') + '</div>');
        }
      },
      error: function(xhr) {
        let msg = 'Server error';
        try { msg = JSON.parse(xhr.responseText).message || msg; } catch(e){}
        $('#msg').html('<div class="alert alert-danger">' + msg + '</div>');
      }
    });
  });

  $('#logoutBtn').click(function() {
    const token = localStorage.getItem('sessionToken');
    if (token) {
      // Optionally notify backend to delete session
      // but we'll just remove localStorage and redirect
      localStorage.removeItem('sessionToken');
      localStorage.removeItem('userName');
    }
    window.location.href = 'login.html';
  });
});
