import { renderHook, act } from '@testing-library/react';
import { useDebounce } from '../index';
import { jest } from '@jest/globals';

describe('useDebounce', () => {
    beforeEach(() => {
        jest.useFakeTimers();
    });

    afterEach(() => {
        jest.useRealTimers();
    });

    it('should debounce function calls', () => {
        const func = jest.fn();
        const { result } = renderHook(() => useDebounce(func, 100));

        act(() => {
            result.current('a');
            result.current('b');
            result.current('c');
        });

        expect(func).not.toHaveBeenCalled();

        act(() => {
            jest.advanceTimersByTime(100);
        });

        expect(func).toHaveBeenCalledTimes(1);
        expect(func).toHaveBeenCalledWith('c');
    });

    it('should cancel pending execution on unmount', () => {
        const func = jest.fn();
        const { result, unmount } = renderHook(() => useDebounce(func, 100));

        act(() => {
            result.current('test');
        });

        unmount();

        act(() => {
            jest.advanceTimersByTime(100);
        });

        expect(func).not.toHaveBeenCalled();
    });
});
