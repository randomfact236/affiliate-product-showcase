import { renderHook } from '@testing-library/react';
import { useProductCache } from '../index';

describe('useProductCache', () => {
    it('should return a SimpleCache instance', () => {
        const { result } = renderHook(() => useProductCache());
        expect(result.current).toBeDefined();
        expect(typeof result.current.get).toBe('function');
        expect(typeof result.current.set).toBe('function');
    });

    it('should persist cache across re-renders', () => {
        const { result, rerender } = renderHook(() => useProductCache());
        const cacheInstance = result.current;

        rerender();

        expect(result.current).toBe(cacheInstance);
    });

    it('should create separate caches for separate hooks', () => {
        const { result: result1 } = renderHook(() => useProductCache());
        const { result: result2 } = renderHook(() => useProductCache());

        expect(result1.current).not.toBe(result2.current);
    });
});
