"use client";

import { useEffect } from "react";

export default function Home() {
    useEffect(() => {
        const fetcher = async () => {
            try {
                const res = await fetch("/api/posts/getPost/index.php?id=1");
                if (!res.ok) {
                    console.log("API fejl:", res.status);
                    return;
                }
                const data = await res.json();
                console.log("API svar:", data);
            } catch (err) {
                console.error("Fetch fejl:", err);
            }
        };

        fetcher();
    }, []);

    return <h1>hellodsaod</h1>;
}
