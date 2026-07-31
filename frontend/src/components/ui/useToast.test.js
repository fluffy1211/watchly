import { renderHook, act } from '@testing-library/react'
import { useToast } from './useToast'

describe('useToast', () => {
  beforeEach(() => {
    jest.useFakeTimers()
  })

  afterEach(() => {
    jest.useRealTimers()
  })

  it('starts with no toasts', () => {
    const { result } = renderHook(() => useToast())
    expect(result.current.toasts).toEqual([])
  })

  it('showToast adds a toast with the given message and variant', () => {
    const { result } = renderHook(() => useToast())

    act(() => {
      result.current.showToast('Saved', 'success')
    })

    expect(result.current.toasts).toHaveLength(1)
    expect(result.current.toasts[0]).toMatchObject({ message: 'Saved', variant: 'success' })
  })

  it('showToast defaults to the info variant', () => {
    const { result } = renderHook(() => useToast())

    act(() => {
      result.current.showToast('Hello')
    })

    expect(result.current.toasts[0].variant).toBe('info')
  })

  it('auto-dismisses a toast after 3 seconds', () => {
    const { result } = renderHook(() => useToast())

    act(() => {
      result.current.showToast('Bye')
    })
    expect(result.current.toasts).toHaveLength(1)

    act(() => {
      jest.advanceTimersByTime(3000)
    })

    expect(result.current.toasts).toHaveLength(0)
  })

  it('removeToast removes a toast by id', () => {
    const { result } = renderHook(() => useToast())

    act(() => {
      result.current.showToast('First')
      result.current.showToast('Second')
    })
    const [first] = result.current.toasts

    act(() => {
      result.current.removeToast(first.id)
    })

    expect(result.current.toasts).toHaveLength(1)
    expect(result.current.toasts[0].message).toBe('Second')
  })
})
