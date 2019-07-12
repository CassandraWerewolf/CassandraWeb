//alert("edit_script.js")

var edit_td
var add = 0;

function change_mode(template_id) {
  new_mode = document.getElementById('mode').value
  location.href="/automod/change_mode.php?template_id="+template_id+"&mode="+new_mode
}

function setup_edit(call_function, td_id, template_id) {
  edit_td = document.getElementById(td_id)
  agent.call('',call_function,'display_td',template_id,'true',add)
}

function add_role(template_id) {
  add++
  setup_edit('create_role_table','role_td',template_id)
}

function display_td(obj) {
  edit_td.innerHTML = obj
}

function cancel(call_function, td_id, template_id) {
  edit_td = document.getElementById(td_id)
  agent.call('',call_function,'display_td',template_id)
  if (td_id == 'role_td') { add = 0; }
}

function delete_me(template_id) {
  del = confirm("Are you sure you want to delete this template?");
  if ( del ) {
    location.href="/automod/delete.php?template_id="+template_id
  }
  return
}

function show_rand_box(count) {
  select1 = 'n0_view_'+count
  select2 = 'rand_choice_'+count
  n0_view_value = document.getElementById(select1).value
  if ( n0_view_value == 'random' ) {
    document.getElementById(select2).style.visibility='visible'
    document.getElementById(select2).style.position='relative'
  } else {
    document.getElementById(select2).style.visibility='hidden'
    document.getElementById(select2).style.position='absolute'
  }
}

function show_positive_box(count) {
  vr = document.getElementById('view_result_'+count).value
  if ( vr == "on" ) {
    document.getElementById('result_'+count).style.visibility='visible'
    document.getElementById('result_'+count).style.position='relative'
    show_free_box('vr','vr_see',count)
  } else {
    document.getElementById('result_'+count).style.visibility='hidden'
    document.getElementById('result_'+count).style.position='absolute'
	document.getElementById('vr_free_text_'+count).style.visibility='hidden'
	document.getElementById('vr_free_text_'+count).style.position='absolute'
  }
}

function show_free_box(id,name,count) {
  free = document.getElementById(name+'_'+count).value
  if ( free == "free" ) {
    document.getElementById(id+'_free_text_'+count).style.visibility='visible'
    document.getElementById(id+'_free_text_'+count).style.position='relative'
  } else {
    document.getElementById(id+'_free_text_'+count).style.visibility='hidden'
    document.getElementById(id+'_free_text_'+count).style.position='absolute'
  }
}
