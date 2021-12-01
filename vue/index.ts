import { createStore } from 'vuex'
import User from '@/store/models/User'
import EventInterface from '@/store/models/EventInterface'

export default createStore({
  state: {
    events: Array<EventInterface>(),
    user: null,
    currentLocation: null
  },
  mutations: {
  },
  getters: {
    getEvent: (state) => (id: number) => {
      return state.events.find(event => event.id === id)
    },
    getEventByLocation: (state) => (longitude: number, latitude: number) => {
      return state.events.filter(event => {
        // expect number in event localization get string
        // eslint-disable-next-line
        return event.localization?.latitude == latitude && event.localization.longitude == longitude
      })
    }
  },
  actions: {
    setPlace ({ state }, payload) {
      state.currentLocation = payload
    },
    addEvent ({ state }, payload) {
      state.events.unshift(payload)
    },
    addCurrentUser ({ state }, payload) {
      state.user = payload
    },
    setEvents ({ state }, payload) {
      state.events = payload
    },
    addUserToEvent ({ state }, payload) {
      const event: EventInterface | undefined = state.events.find((item: EventInterface) => item.id === payload.event.id)
      if (event !== undefined) {
        const user: User | undefined = event.invited.find((item: User) => item.id === payload.user.id)
        if (user === undefined) {
          event.invited.unshift(payload.user)
        }
      }
    },
    joinUserToEvent ({ state }, payload) {
      const event: EventInterface | undefined = state.events.find((item: EventInterface) => item.id === payload.event.id)
      if (event !== undefined) {
        const invited: User | undefined = event.invited.find((item: User) => item.id === payload.user.id)
        if (invited !== undefined) {
          event.invited = event.invited.filter(user => user.id !== invited.id)
        }
        const participant: User | undefined = event.participants.find((item: User) => item.id === payload.user.id)
        if (participant === undefined) {
          event.participants.push(payload.user)
        }
      }
    },
    removeUserFromInvitedEvent ({ state }, payload) {
      const event: EventInterface | undefined = state.events.find((item: EventInterface) => item.id === payload.event.id)
      if (event !== undefined) {
        event.invited = event.invited.filter((u: User) => u.id !== payload.user.id)
      }
    },
    removeUserFromParticipantEvent ({ state }, payload) {
      const event: EventInterface | undefined = state.events.find((item: EventInterface) => item.id === payload.event.id)
      if (event !== undefined) {
        event.participants = event.participants.filter((u: User) => u.id !== payload.user.id)
      }
    }
  },
  modules: {
  }
})
