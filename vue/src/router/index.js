import { createRouter, createWebHistory } from 'vue-router'
import HomeView from '../views/HomeView.vue'
import MenuManager from '../views/MenuManager.vue'
import MediaManager from '../views/MediaManager.vue'
import PostManager from '../views/PostManager.vue'
import AuthView from '../views/AuthView.vue'

const router = createRouter({
  history: createWebHistory(import.meta.env.BASE_URL),
  routes: [
    {
      path: '/',
      name: 'home',
      component: HomeView,
    },
    {
      path:'/login',
      name:'login',
      component: AuthView,
    },
    {
      path: '/about',
      name: 'about',
      // route level code-splitting
      // this generates a separate chunk (About.[hash].js) for this route
      // which is lazy-loaded when the route is visited.
      component: () => import('../views/AboutView.vue'),
    },
{
      path: '/menu',
      name: 'menu',
      component: MenuManager // Báo cho Router biết đường dẫn /menu sẽ mở component này
    },
    {
      path: '/media',
      name: 'media',
      component: MediaManager
    },
    {
      path: '/post',
      name: 'post',
      component: PostManager
    },
    {
      path: '/post/:id',
      name: 'post-detail',
      component: () => import('../views/PostDetail.vue')
    }

  ],
})

export default router
