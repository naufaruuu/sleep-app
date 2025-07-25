@if ($crud->hasAccess('create'))
    @php
        $key = $entry->getKey();
        if($entry instanceof \App\Models\ModelMappingInterface) {
            $table_name = $entry->getApzEloquentClassName();
        }
    @endphp

    <label class="switch">
        <input data-id="{{ $key }}" id="toggle-class-{{ $key }}" onchange="fixed_sleep(this)" type="checkbox" data-onstyle="success" data-offstyle="danger" data-toggle="toggle" data-on="On" data-off="Off" {{ $entry->isFixed ? 'checked' : '' }}>
        <span class="slider round"></span>
    </label>


<script>
  function fixed_sleep(val) {
      var status = val.checked == true ? 1 : 0;
      var id = val.id;

      id = id.split("-");
      id = id[2];

      $.ajax({
          type: "POST",
          dataType: "json",
          url: "/admin/project/fixed_sleep",
          data: {'status': status ? "1" : "0", 'id': id},
          success: function(data) {
            var notification_type;

            if (data.success) {
              notification_type = 'success';
            } else {
              notification_type = 'alert';
            }

            new Noty({
              text: data.message,
              type: notification_type,
			}).show();
          },
          error: function(data) {
              console.log(data);
              $(`#${val.id}`).prop("checked", !val.checked);
              new Noty({
                text: 'Something error!',
                type: 'error',
              }).show();
          }
      });
  }
</script>
@endif


<style>
    /* The switch - the box around the slider */
    .switch {
        position: relative;
        display: inline-block;
        width: 60px;
        height: 34px;
    }

    /* Hide default HTML checkbox */
    .switch input {
        opacity: 0;
        width: 0;
        height: 0;
    }

    /* The slider */
    .slider {
        position: absolute;
        cursor: pointer;
        top: 0;
        left: 0;
        right: 0;
        bottom: 0;
        background-color: #ccc;
        -webkit-transition: .4s;
        transition: .4s;
    }

    .slider:before {
        position: absolute;
        content: "";
        height: 26px;
        width: 26px;
        left: 4px;
        bottom: 4px;
        background-color: white;
        -webkit-transition: .4s;
        transition: .4s;
    }

    input:checked + .slider {
        background-color: #21f383;
    }

    input:focus + .slider {
        box-shadow: 0 0 1px #21f383;
    }

    input:checked + .slider:before {
        -webkit-transform: translateX(26px);
        -ms-transform: translateX(26px);
        transform: translateX(26px);
    }

    /* Rounded sliders */
    .slider.round {
        border-radius: 34px;
    }

    .slider.round:before {
        border-radius: 50%;
    }
</style>