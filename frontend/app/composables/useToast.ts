export type ToastTone = 'success' | 'error' | 'info'

export type AppToastMessage = {
  id: string
  title: string
  message: string
  tone: ToastTone
  duration: number
}

export const useToast = () => {
  const toast = useState<AppToastMessage | null>('app-toast-message', () => null)

  const show = (title: string, message: string, tone: ToastTone = 'success', duration = 3500) => {
    toast.value = {
      id: `${Date.now()}-${Math.random().toString(36).slice(2)}`,
      title,
      message,
      tone,
      duration,
    }
  }

  const dismiss = (id?: string) => {
    if (!id || toast.value?.id === id) toast.value = null
  }

  return {
    toast,
    showToast: show,
    success: (title: string, message: string, duration?: number) => show(title, message, 'success', duration),
    error: (title: string, message: string, duration?: number) => show(title, message, 'error', duration),
    info: (title: string, message: string, duration?: number) => show(title, message, 'info', duration),
    dismissToast: dismiss,
  }
}
