export default function SectionHeading({ children }: { children: string }) {
    return (
        <h2 className="flex items-center gap-3 text-2xl font-bold tracking-tight">
            <span
                aria-hidden="true"
                className="inline-block h-1 w-10 rounded-full bg-sky-600"
            />
            {children}
        </h2>
    );
}
