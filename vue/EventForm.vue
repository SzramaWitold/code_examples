<template>
  <div class="event-form-container">
    <form @submit.prevent>
      <input :class="{'not_valid': v$.event.name.$error }" type="text" id="fname" v-model="event.name" name="firstname" :placeholder="$t('form.placeholder.name')">
      <span v-if="v$.event.name.$error">
        {{ $t('form.validation.' + v$.event.name.$errors[0].$params.type , v$.event.name.$errors[0].$params) }}
      </span>
      <input :class="{'not_valid': v$.event.place.$error }" type="text" id="place" v-model="event.place" name="place" :placeholder="$t('form.placeholder.place')">
      <span v-if="v$.event.place.$error">
        {{ $t('form.validation.' + v$.event.place.$errors[0].$params.type , v$.event.place.$errors[0].$params) }}
      </span>
      <textarea :class="{'not_valid': v$.event.description.$error }"  id="description" v-model="event.description" :placeholder="$t('form.placeholder.description')"/>
      <span v-if="v$.event.description.$error">
        {{ $t('form.validation.' + v$.event.description.$errors[0].$params.type , v$.event.description.$errors[0].$params )}}
      </span>
      <div class="grid">
        <div class="clean_space">
          <Datepicker ref="regEnd" uid="register-end" :class="{'not_valid': v$.event.register_end.$error }" :placeholder="$t('form.placeholder.registerEnd')" class="clean_space"  v-model="event.register_end"/>
          <span v-if="v$.event.register_end.$error">
            {{ $t('form.validation.' + v$.event.register_end.$errors[0].$params.type, v$.event.register_end.$errors[0].$params) }}
          </span>
        </div>
        <div class="clean_space">
          <input :class="{'not_valid': v$.event.max_people.$error }" type="number" name="place" v-model="event.max_people" :placeholder="$t('form.placeholder.maxPeople')">
          <span v-if="v$.event.max_people.$error">
            {{ $t('form.validation.' + v$.event.max_people.$errors[0].$params.type, v$.event.max_people.$errors[0].$params) }}
          </span>
        </div>
      </div>
      <div class="grid">
        <div class="clean_space">
          <Datepicker ref="start" uid="start" :class="{'not_valid': v$.event.start.$error }" :placeholder="$t('form.placeholder.start')" class="clean_space"  v-model="event.start"/>
          <span v-if="v$.event.start.$error">
            {{ $t('form.validation.' + v$.event.start.$errors[0].$params.type, v$.event.start.$errors[0].$params) }}
          </span>
        </div>
        <div class="clean_space">
          <Datepicker ref="end" uid="end" :class="{'not_valid': v$.event.end.$error }"  :placeholder="$t('form.placeholder.end')" class="clean_space" v-model="event.end"/>
          <span v-if="v$.event.end.$error">
            {{ $t('form.validation.' + v$.event.end.$errors[0].$params.type, v$.event.end.$errors[0].$params) }}
          </span>
        </div>
      </div>
      <select :class="{'not_valid': v$.event.type.$error }" id="type" v-model="event.type">
        <option value="" disabled>{{ $t('form.placeholder.selectType') }}</option>
        <option value="public">{{ $t('public') }}</option>
        <option value="private">{{ $t('private') }}</option>
      </select>
      <span v-if="v$.event.type.$error">
            {{ $t('form.validation.' + v$.event.type.$errors[0].$params.type, v$.event.type.$errors[0].$params) }}
      </span>
      <button @click="onSubmit()">{{ $t('addEvent').toUpperCase()}}</button>
    </form>
  </div>
</template>

<script lang="ts">
import { Options, Vue } from 'vue-class-component'
import Datepicker from 'vue3-date-time-picker'
import 'vue3-date-time-picker/dist/main.css'
import EventModel from '@/store/models/EventModel'
import store from '@/store'
import { required, helpers, minValue } from '@vuelidate/validators'
import { useVuelidate } from '@vuelidate/core'
import { api } from '@/plugins/axios.ts'
import EventInterface from '@/store/models/EventInterface'

@Options({
  components: {
    Datepicker
  },
  validations: {
    event: {
      name: {
        required: helpers.withMessage('form.validation.required', required),
        betweenLength: helpers.withParams(
          { type: 'betweenLength', min: 1, max: 100 },
          (value: string) => value.length > 1 && value.length < 100
        )
      },
      place: {
        required: helpers.withMessage('form.validation.required', required),
        betweenLength: helpers.withParams(
          { type: 'betweenLength', min: 1, max: 100 },
          (value: string) => value.length > 1 && value.length < 100
        )
      },
      description: {
        betweenLength: helpers.withParams(
          { type: 'betweenLength', min: 1, max: 1000 },
          (value: string) => value.length === 0 || (value.length > 1 && value.length < 1000)
        )
      },
      start: {
        required: helpers.withMessage('form.validation.required', required),
        afterNowDate: helpers.withParams(
          { type: 'afterNowDate' },
          (value: Date) => value >= new Date()
        )
      },
      end: {
        required: helpers.withMessage('form.validation.required', required),
        afterStartDate: helpers.withParams(
          { type: 'afterStartDate' },
          (value: Date, vm) => value >= vm.event.start
        )
      },
      register_end: {
        afterStartDate: helpers.withParams(
          { type: 'beforeStartDate' },
          (value: Date, vm) => value < vm.event.start
        )
      },
      max_people: {
        required: helpers.withMessage('form.validation.required', required),
        minValue: minValue(1)
      },
      type: {
        required: helpers.withMessage('form.validation.required', required)
      }
    }
  }
})

export default class EventForm extends Vue {
  public event: EventInterface = new EventModel()
  $refs!: {
    start: HTMLFormElement
    end: HTMLFormElement
    regEnd: HTMLFormElement
  }

  // todo check for better solution because useVuelidate expect return ref and in method it return normal object
  public v$ = useVuelidate() as any

  onSubmit () {
    this.v$.$validate()
    if (!this.v$.$error) {
      this.event.localization = store.state.currentLocation
      api.post('/api/events/add', this.event).then((response: any) => {
        store.dispatch('addEvent', response.data.data)
      })
      this.v$.$reset()
      this.event = new EventModel()
      this.$refs.regEnd.clearValue()
      this.$refs.start.clearValue()
      this.$refs.end.clearValue()
      this.$router.push('/')
    }
  }
}
</script>

<!-- Add "scoped" attribute to limit CSS to this component only -->
<style scoped>

.grid {
  display: grid;
  grid-template-columns: 1fr 1fr;
  gap: 5%;
  margin: 0;
  padding: 0;
}
input[type=text], select, textarea, input[type=number]  {
  width: 100%;
  padding: 12px 20px;
  margin: 8px 0;
  display: inline-block;
  border: none;
  border-radius: 4px;
  box-sizing: border-box;
}

textarea {
  min-height: 200px;
}

#type {
  background: ghostwhite;
}

button {
  width: 100%;
  background-color: #0f6674;
  color: white;
  padding: 14px 20px;
  margin: 8px 0;
  border: none;
  border-radius: 4px;
  cursor: pointer;
}

button:hover {
  background-color: #45a049 !important;
}

div {
  border-radius: 5px;
  background-color: #f2f2f2;
  /*padding: 20px;*/
}

.event-form-container {
  padding: 5%;
}
</style>
