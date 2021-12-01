import axios, { AxiosResponse } from 'axios'

const api = axios.create({
  baseURL: 'http://localhost:8080',
  headers: {
    Accept: 'application/json',
    'Access-Control-Allow-Origin': '*'
  }
})

const oauth = axios.create({
  baseURL: 'http://localhost:8080',
  headers: {
    Accept: 'application/json',
    'Access-Control-Allow-Origin': '*'
  }
})

api.interceptors.request.use((request) => {
  if (request.headers && request.url !== '/oauth/token') {
    request.headers.Authorization = 'Bearer ' + localStorage.getItem('token')
  }

  return request
})

api.interceptors.response.use(function (response) {
  // Do something with response data
  return response
}, function (error) {
  if (error.response.data.message === 'Unauthenticated.') {
    if (localStorage.getItem('oauth-token')) {
      api.get('api/auth/' + localStorage.getItem('oauth-token'))
        .then((response: AxiosResponse<any>) => {
          localStorage.setItem('token', response.data.access_token)
          localStorage.setItem('refreshToken', response.data.refresh_token)
          localStorage.removeItem('oauth-token')

          return api.request(error.config)
        })
        .catch((e) => {
          localStorage.removeItem('oauth-token')
        })
    } else {
      oauth.post('/oauth/token', {
        grant_type: 'refresh_token',
        client_id: process.env.VUE_APP_OAUTH_CLIENT_ID ?? 2,
        client_secret: process.env.VUE_APP_OAUTH_SECRET,
        refresh_token: localStorage.getItem('refreshToken')
      }).then((response: any) => {
        localStorage.setItem('token', response.data.access_token)
        localStorage.setItem('refreshToken', response.data.refresh_token)

        return api.request(error.config)
      })
    }
  }

  return Promise.reject(error)
})

const positionApi = axios.create({
  baseURL: process.env.VUE_APP_POSITION_STACK_URL,
  params: {
    access_key: process.env.VUE_APP_POSITION_STACK_KEY,
    limit: 1,
    output: 'json'
  }
})

export { api, positionApi }
