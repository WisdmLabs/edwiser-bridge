declare const throttle: (func: () => void, limit: number, setIsInThrottle?: ((value?: boolean | undefined) => void) | undefined) => () => void;
export default throttle;
