<template>
  <div class="margin-center">
    <h2 v-if="events.length > 0" >{{ $t('eventList') }}</h2>
    <table v-if="events.length > 0" id="customers">
      <tr class="tr-filter">
        <td class="td-filters">
        </td>
        <td class="td-filters">
          <Datepicker uid="filter-register-end" class="datepicker" :enableTimePicker="false" v-model="filters.register_end" @update:modelValue="changePage"/>
        </td>
        <td class="td-filters">
          <Datepicker uid="filter-start" class="datepicker" :enableTimePicker="false" v-model="filters.start" @update:modelValue="changePage"/>
        </td>
        <td class="td-filters">
          <Datepicker uid="filter-end" class="datepicker" :enableTimePicker="false" v-model="filters.end" @update:modelValue="changePage"/>
        </td>
        <td class="td-filters">
          <select v-if="user" class="filters"  @change="changePage" id="type" v-model="filters.type">
            <option value="">{{ $t('all') }}</option>
            <option value="public">{{ $t('public') }}</option>
            <option value="private">{{ $t('private') }}</option>
          </select>
        </td>
      </tr>
      <tr>
        <th class="title">{{ $t('event.event') }}</th>
        <th>{{ $t('event.registration') }}</th>
        <th>{{ $t('event.start') }}</th>
        <th>{{ $t('event.end') }}</th>
        <th>{{ $t('event.type') }}</th>
        <th>{{ $t('event.participants') }}</th>
        <th>{{ $t('event.invited') }}</th>
        <th></th>
      </tr>
      <tr v-for="(event, key) in events" :key="'event' + key">
        <td class="title">{{ event.name }}</td>
        <td>
          <div class="tooltip">
            {{ moment(event.register_end).locale(locale).fromNow() }}
            <span class="tooltiptext">{{ moment().format(event.register_end) }}</span>
          </div>
        </td>
        <td>
          <div class="tooltip">
          {{ moment(event.start).locale(locale).fromNow() }}
            <span class="tooltiptext">{{ moment().format(event.start) }}</span>
          </div>
        </td>
        <td>
          <div class="tooltip">
            {{ moment(event.end).locale(locale).fromNow() }}
            <span class="tooltiptext">{{ moment().format(event.end) }}</span>
          </div>
        </td>
        <td>{{ $t(event.type) }}</td>
        <td>
          <div v-if="event.participants.length > 0" class="dropdown">
            <span>{{ event.participants[0].username }}</span>
            <div class="dropdown-content">
              <EventUserList type="participants" :event="event"></EventUserList>
            </div>
          </div>
        </td>
        <td>
          <div v-if="event.invited.length > 0" class="dropdown">
            <span :class="isMe(event.invited[0]) ? 'me' : '' ">{{event.invited[0].username }}</span>
            <div class="dropdown-content">
              <EventUserList type="invited" :event="event"></EventUserList>
              <div v-if="isEventOwner(event)" class="tooltip add-btn pa-10" @click="prepareDialog(event)">
                <user-plus-icon size="20" class="list-icon"></user-plus-icon>
                <span class="tooltiptext">+ {{ $t('invite') }}</span>
              </div>
            </div>
          </div>
          <div v-if="event.invited.length === 0"  class="add-btn" @click="prepareDialog(event)">
            <div v-if="isEventOwner(event)" class="tooltip add-btn" @click="prepareDialog(event)">
              <user-plus-icon  size="20" class="list-icon"></user-plus-icon>
              <span class="tooltiptext">+ {{ $t('invite') }}</span>
            </div>
          </div>
        </td>
        <td>
          <div v-if="canJoin(event)"  @click="joinToEvent(event)" class="tooltip pointer add-btn">
            <arrows-join-icon class="list-icon"></arrows-join-icon>
            <span class="tooltiptext">{{ $t('event.join')}}</span>
          </div>
          <router-link :to="{ name: 'Details', params:{ id:event.id }}"  class="tooltip pointer add-btn">
            <id-icon class="list-icon"></id-icon>
            <span class="tooltiptext">{{ $t('details')}}</span>
          </router-link>
        </td>
      </tr>
      <tfoot id="event-table-footer">
        <tr>
          <td></td>
          <td></td>
          <td></td>
          <td></td>
          <td></td>
          <td></td>
          <td></td>
          <td>
            <div >
              <select class="pointer" @change="changePage" v-model="currentPage">
                <option :selected="page === currentPage" v-for="page in totalPages" :key="'pages_' + page ">{{ page }}</option>
              </select>
            </div>
          </td>
        </tr>
      </tfoot>
    </table>
    <div v-else><h3>{{ $t('event.empty') }}</h3></div>
    <GDialog v-model="openSearchUser" max-width="500">
      <GetUserForm :event="chosenEvent"></GetUserForm>
    </GDialog>
  </div>
</template>

<script lang="ts">
import { Options, Vue } from 'vue-class-component'
import store from '@/store'
import EventUserList from '@/components/lists/EventUserList.vue'
import { api } from '@/plugins/axios'
import moment from 'moment'
import { GDialog } from 'gitart-vue-dialog'
import GetUserForm from '@/components/forms/GetUserForm.vue'
import EventModel from '@/store/models/EventModel'
import Datepicker from 'vue3-date-time-picker'
import { IdIcon, UserPlusIcon, ArrowsJoinIcon } from 'vue-tabler-icons'
import i18n from '@/i18n'
import EventInterface from '@/store/models/EventInterface'
import * as qs from 'qs'
import User from '@/store/models/User'

@Options({
  components: {
    EventUserList,
    GDialog,
    GetUserForm,
    Datepicker,
    IdIcon,
    UserPlusIcon,
    ArrowsJoinIcon
  },
  watch: {
    currentLocation () {
      this.locationChange()
    }
  }
})
export default class EventList extends Vue {
  public moment = moment
  public language = i18n
  public currentPage = 1
  public totalPages = 1
  public perPage = 10
  public openSearchUser = false
  public chosenEvent: EventModel | null = null
  public filters = {
    type: '',
    register_end: null,
    end: null,
    start: null
  }

  get events () {
    return store.state.events
  }

  get currentLocation () {
    return store.state.currentLocation
  }

  get user (): any {
    return store.state.user
  }

  get locale (): string {
    return this.language.global.locale
  }

  isMe (user: User):boolean {
    if (this.user) {
      return this.user.id === user.id
    }
    return false
  }

  locationChange () {
    if (this.currentLocation !== null) {
      api.get('/api/events', {
        params: {
          page: this.currentPage,
          perPage: this.perPage,
          filters: this.filters,
          localization: this.currentLocation
        },
        paramsSerializer: function (params) {
          return qs.stringify(params, { encode: false })
        }
      }).then((response: any) => {
        this.totalPages = response.data.meta.last_page
        store.dispatch('setEvents', response.data.data as unknown[])
      })
    }
  }

  isEventOwner (event: EventInterface): boolean {
    if (this.user === null) {
      return false
    }

    if (event.participants.filter(u => u.id === this.user.id).length === 0) {
      return false
    }

    return true
  }

  userInvited (event: EventInterface):boolean {
    if (!this.user) {
      return false
    }

    return event.invited.some(u => u.id === this.user.id)
  }

  joinToEvent (event: EventInterface): void {
    api.put('api/events/' + event.id + '/join')
    store.dispatch('joinUserToEvent', {
      event: event,
      user: this.user
    })
  }

  canJoin (event: EventInterface): boolean {
    if (!this.user) {
      return false
    }
    if (this.userParticipate(event)) {
      return false
    }
    if (this.userInvited(event)) {
      return true
    }
    return event.type === 'public'
  }

  userParticipate (event: EventInterface): boolean {
    if (!this.user) {
      return false
    } else {
      return event.participants.some(u => u.id === this.user.id)
    }
  }

  userOwner (event: EventModel): boolean {
    if (!this.user) {
      return false
    } else {
      return event.participants.some(u => u.id === this.user.id && u.type === 'owner')
    }
  }

  prepareDialog (event: EventModel) {
    this.openSearchUser = true
    this.chosenEvent = event
  }

  changePage () {
    if (this.currentLocation !== null) {
      api.get('/api/events', {
        params: {
          page: this.currentPage,
          perPage: this.perPage,
          filters: this.filters,
          localization: this.currentLocation
        },
        paramsSerializer: function (params) {
          return qs.stringify(params, { encode: false })
        }
      }).then((response: any) => {
        this.totalPages = response.data.meta.last_page
        store.dispatch('setEvents', response.data.data as unknown[])
      })
    }
  }
}
</script>

<!-- Add "scoped" attribute to limit CSS to this component only -->
<style scoped>
#customers {
  font-family: Arial, Helvetica, sans-serif;
  border-collapse: collapse;
  width: 95%;
  margin-left: auto;
  margin-right: auto;
}

#customers td, #customers th {
  border: 1px solid #ddd;
  padding: 4px;
}

.list-icon {
  color: #2c3e50;
  padding: 0px 3px;
}

.list-icon:hover {
  color: darkgreen;
}

#customers tr:nth-child(even){background-color: #f2f2f2;}

#customers tr:hover {
  background-color: #ddd;
}
.add-btn:hover {
  background-color: #ddd;
}
#customers th {
  padding-top: 12px;
  padding-bottom: 12px;
  text-align: left;
  background-color: #2c3e50;
  color: white;
}
#event-table-footer td {
  border: none !important;
  margin: 0;
  padding: 0;
  text-align: right;
}

#event-table-footer tr:hover {
  background: transparent!important;
}

.filters {
  width: 100%;
  border: none !important;
  cursor: pointer;
}

.td-filters {
  margin: 0 !important;
  padding: 0 !important;
  border: none !important;
}

.td-filters:hover, .filters:hover , .tr-filter:hover {
  background-color: transparent !important;
}
th, td, .datepicker {
  min-width: 100px;
  max-width: 200px;
}
.title {
  max-width: 500px!important;
}

select {
  margin: 0;
  padding: 12px;
  height: 50px;
  border: none;
  display: inline-block;
  background: ghostwhite;
}

</style>
